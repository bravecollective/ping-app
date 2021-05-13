<?php

use Brave\PingApp\Controller\IndexController;
use Brave\PingApp\Controller\PingController;
use Brave\PingApp\Controller\AuthenticationController;
use Psr\Container\ContainerInterface;
use Slim\App;

return function (ContainerInterface $container)
{
    /** @var App $app */
    $app = $container->get(App::class);

    // SSO via sso-basics package
    $app->get('/', IndexController::class);
    $app->get('/login', AuthenticationController::class . ':index');
    $app->get('/auth', AuthenticationController::class . ':callback');
    $app->get('/logout', AuthenticationController::class . ':logout');

    $app->get('/ping/new', PingController::class . ':index');
    $app->post('/ping/send', PingController::class . ':send');

    return $app;
};
