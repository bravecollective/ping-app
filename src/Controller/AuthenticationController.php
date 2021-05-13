<?php
/** @noinspection PhpUnused */

namespace Brave\PingApp\Controller;

use Brave\PingApp\RoleProvider;
use Eve\Sso\AuthenticationProvider;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;
use SlimSession\Helper;

class AuthenticationController
{
    /**
     * @var Helper
     */
    private $sessionHandler;

    /**
     * @var RoleProvider
     */
    private $roleProvider;

    /**
     * @var AuthenticationProvider
     */
    private $authenticationProvider;

    /**
     * @var string
     */
    private $serviceName;

    /**
     * @var string
     */
    private $template = ROOT_DIR . '/html/ping/sso_page.html';

    public function __construct(ContainerInterface $container)
    {
        $this->sessionHandler = $container->get(Helper::class);
        $this->roleProvider = $container->get(RoleProvider::class);
        $this->authenticationProvider = $container->get(AuthenticationProvider::class);
        $this->serviceName = isset($container->get('settings')['brave.serviceName']) ?
            (string) $container->get('settings')['brave.serviceName'] :
            'Brave Service';
    }

    /**
     * Show the login page.
     *
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $state = $this->authenticationProvider->generateState();
        $this->sessionHandler->set('ssoState', $state);

        $loginUrl = $this->authenticationProvider->buildLoginUrl($state);

        $templateCode = file_get_contents($this->template);

        $body = str_replace(
            ['{{serviceName}}', '{{loginUrl}}'],
            [$this->serviceName, $loginUrl],
            $templateCode
        );

        $response->getBody()->write($body);

        return $response;
    }

    public function callback(ServerRequestInterface $request, Response $response): ResponseInterface
    {
        $queryParameters = $request->getQueryParams();

        if (!isset($queryParameters['code']) || !isset($queryParameters['state'])) {
            error_log('Invalid SSO state, please try again.');
            return $response;
        }

        $code = $queryParameters['code'];
        $state = $queryParameters['state'];

        $sessionState = $this->sessionHandler->get('ssoState');
        $eveAuthentication = $this->authenticationProvider->validateAuthenticationV2($state, $sessionState, $code);

        $this->sessionHandler->set('ssoState', null);
        $this->sessionHandler->set('eveAuth', $eveAuthentication);

        $this->roleProvider->clear();

        return $response->withRedirect('/ping/new');
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function logout(ServerRequestInterface $request, Response $response): ResponseInterface
    {
        $this->sessionHandler->clear();

        return $response->withRedirect('/login');
    }
}
