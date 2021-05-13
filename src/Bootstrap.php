<?php

namespace Brave\PingApp;

use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Middleware\Session;
use Tkhamez\Slim\RoleAuth\RoleMiddleware;
use Tkhamez\Slim\RoleAuth\SecureRouteMiddleware;

class Bootstrap
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(require_once(ROOT_DIR . '/config/container.php'));
        $this->container = $containerBuilder->build();
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

        $app->add(new SecureRouteMiddleware(
            $this->container->get(ResponseFactoryInterface::class),
            include ROOT_DIR . '/config/security.php',
            ['redirect_url' => '/login']
        ));
        $app->add(new RoleMiddleware($this->container->get(RoleProvider::class)));

        $app->add(new Session([
            'name' => 'brave_service',
            'autorefresh' => true,
            'lifetime' => '1 hour'
        ]));

        $app->addRoutingMiddleware();
        $app->addErrorMiddleware(false, true, true);

        return $app;
    }
}
