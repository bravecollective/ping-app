<?php
namespace Brave\PingApp\Controller;

use Brave\PingApp\Entity\Ping;
use Brave\PingApp\Repository\PingRepository;
use Brave\PingApp\Security;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 *
 */
class PingController
{
    /**
     * ContainerInterface
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Security
     */
    protected $security;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->security = $container->get(Security::class);
    }

    /**
     * Undocumented function
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $arguments
     * @return ResponseInterface
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response, $arguments)
    {
        $serviceName = isset($this->container->get('settings')['brave.serviceName']) ? $this->container->get('settings')['brave.serviceName'] : 'Brave Service';

        $allPingGroups = $this->security->getAllowedPingGroups();
        $channelMap = $this->container->get('settings')['channelMapping'];

        $pingGroupOptions = implode("\n", array_map(function ($groupName) use ($channelMap) {
            return '<option value="' . $groupName . '">' . $groupName . ' (#'. $channelMap[$groupName] .')</option>';
        }, $allPingGroups));

        $templateCode = file_get_contents(__DIR__ . '/../../html/ping/index.html');

        $body = str_replace([
            '{{serviceName}}',
            '{{groups}}',
            '{{templates}}',
        ], [
            $serviceName,
            $pingGroupOptions,
            htmlspecialchars(json_encode($this->container->get('settings')['templates'])),
        ], $templateCode);

        return $response->getBody()->write($body);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function send(ServerRequestInterface $request, ResponseInterface $response)
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

        /** @var EntityManager $em */
        $em = $this->container->get(EntityManagerInterface::class);
        $em->persist($ping);
        $em->flush();

        $this->sendPingToSlack($ping);

        return $response->withRedirect('/ping/new', 301);
    }

    private function sendPingToSlack(Ping $ping)
    {
        $url = $this->container->get('settings')['SLACKBOT_URL'];
        $channelName = $this->getPingChannel($ping->group);
        $pingText = "@channel PING \n\n" . $ping->text . $this->getPingMetadata($ping);

        $guzzleClient = new Client();
        $guzzleClient->request('POST', $url . '&channel=' . $channelName, [
            'body' => $pingText
        ]);
    }

    private function getPingMetadata(Ping $ping)
    {
        return "\n\n" . sprintf('> %s - %s TO %s', $ping->dateTime->format('Y-m-d H:i:s'), $ping->character, $ping->group);
    }

    /**
     * @param $pingGroup
     * @return string
     * @throws \Exception
     */
    private function getPingChannel($pingGroup)
    {
        $mapping = $this->container->get('settings')['channelMapping'];

        if (!isset($mapping[$pingGroup])) {
            throw new \Exception('Could not map ping group to channel.');
        }

        return $mapping[$pingGroup];
    }
}
