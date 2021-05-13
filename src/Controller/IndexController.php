<?php

namespace Brave\PingApp\Controller;

use Eve\Sso\EveAuthentication;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlimSession\Helper;

class IndexController
{
    /**
     * @var Helper
     */
    private $sessionData;

    public function __construct(ContainerInterface $container)
    {
        $this->sessionData = $container->get(Helper::class);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($this->sessionData->get('eveAuth') instanceof EveAuthentication) {
            $redirect = '/ping/new';
        } else {
            $redirect = '/login';
        }

        return $response->withHeader('Location', $redirect);
    }
}
