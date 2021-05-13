<?php

namespace Brave\PingApp;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Container;
use Slim\Middleware\Session;
use Tkhamez\Slim\RoleAuth\RoleMiddleware;
use Tkhamez\Slim\RoleAuth\SecureRouteMiddleware;

class Bootstrap
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct()
    {
        $container = new Container(require_once(ROOT_DIR . '/config/container.php'));
        $this->container = $container;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function enableRoutes(): App
    {
        /** @var App $app */
        $routesConfigurator = require_once(ROOT_DIR . '/config/routes.php');
        $app = $routesConfigurator($this->container);

        $app->add(new SecureRouteMiddleware(include ROOT_DIR . '/config/security.php', ['redirect_url' => '/login']));
        $app->add(new RoleMiddleware($this->container->get(RoleProvider::class)));

        $app->add(new Session([
            'name' => 'brave_service',
            'autorefresh' => true,
            'lifetime' => '1 hour'
        ]));

        return $app;
    }
}
