<?php
namespace Brave\PingApp\Controller;

use Brave\PingApp\Entity\Ping;
use Brave\PingApp\Security;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PingController
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var Security
     */
    protected $security;

    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get(EntityManagerInterface::class);
        $this->settings = $container->get('settings');
        $this->security = $container->get(Security::class);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $serviceName = $this->settings['app.serviceName'] ?? 'Brave Service';

        $pingGroups = $this->security->getAllowedPingGroups();
        sort($pingGroups);
        $channelMap = $this->settings['channelMapping'];

        // read templates of allowed ping groups
        $templates = [];
        foreach ($this->settings['templates'] as $name => $template) {
            if ($name === '__default' || in_array($name, $pingGroups)) {
                $templates[$name] = $template;
            }
        }

        $pingGroupOptions = implode("\n", array_map(function ($groupName) use ($channelMap) {
            return '<option value="' . $groupName . '">' . $groupName . ' (#'. $channelMap[$groupName] .')</option>';
        }, $pingGroups));

        $body = str_replace(
            ['{{serviceName}}', '{{groups}}', '{{templates}}'],
            [$serviceName, $pingGroupOptions, htmlspecialchars(json_encode($templates))],
            file_get_contents(__DIR__ . '/../../html/ping/index.html')
        );

        $response->getBody()->write($body);
        return $response;
    }

    public function send(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $characterName = $this->security->getAuthorizedName();
        $parsedBody = $request->getParsedBody();

        $ping = new Ping();
        $ping->text = $parsedBody['text'];
        $ping->group = $parsedBody['group'];
        $ping->character = $characterName;

        $allPingGroups = $this->security->getAllowedPingGroups();
        if (!in_array($ping->group, $allPingGroups)) {
            return $response->withStatus(403);
        }

        $this->em->persist($ping);
        $this->em->flush();

        $success = true;
        try {
            $this->sendPingToSlack($ping);
        } catch (GuzzleException | Exception $e) {
            error_log((string)$e);
            $success = false;
        }

        return $response
            ->withHeader('Location', '/ping/new' . ($success ? '#ping-sent' : ''))
            ->withStatus(301);
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    private function sendPingToSlack(Ping $ping)
    {
        $url = $this->settings['SLACKBOT_URL'];
        $channelName = $this->getPingChannel($ping->group);
        $pingText = "@channel PING \n\n" . $ping->text . $this->getPingMetadata($ping);

        $guzzleClient = new Client();
        $guzzleClient->request('POST', $url . '&channel=' . $channelName, [
            'body' => $pingText
        ]);
    }

    private function getPingMetadata(Ping $ping): string
    {
        return "\n\n" . sprintf(
            '> %s - %s TO %s',
            $ping->dateTime->format('Y-m-d H:i:s'),
            $ping->character,
            $ping->group
        );
    }

    /**
     * @param $pingGroup
     * @return string
     * @throws Exception
     */
    private function getPingChannel($pingGroup): string
    {
        $mapping = $this->settings['channelMapping'];

        if (!isset($mapping[$pingGroup])) {
            throw new Exception('Could not map ping group to channel.');
        }

        return $mapping[$pingGroup];
    }
}
