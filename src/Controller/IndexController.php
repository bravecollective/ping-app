<?php

namespace Brave\PingApp\Controller;

use Brave\Sso\Basics\EveAuthentication;
use Brave\Sso\Basics\SessionHandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IndexController
{
    /**
     * @var SessionHandlerInterface
     */
    private $sessionData;

    public function __construct(ContainerInterface $container)
    {
        $this->sessionData = $container->get(SessionHandlerInterface::class);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        if ($this->sessionData->get('eveAuth', null) instanceof EveAuthentication) {
            $redirect = '/ping/new';
        } else {
            $redirect = '/login';
        }

        return $response->withHeader('Location', $redirect);
    }
}
