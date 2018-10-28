<?php
use Brave\PingApp\Controller\PingController;
use Brave\PingApp\Controller\AuthenticationController;

return function (\Psr\Container\ContainerInterface $container)
{
    /** @var \Slim\App $app */
    $app = $container[\Slim\App::class];

    // SSO via sso-basics package
    $app->get('/', AuthenticationController::class . ':index');
    $app->get('/login', AuthenticationController::class . ':index');
    $app->get('/auth', AuthenticationController::class . ':callback');
    $app->get('/logout', AuthenticationController::class . ':logout');

    $app->get('/ping/new', PingController::class . ':index');
    $app->post('/ping/send', PingController::class . ':send');

    return $app;
};
