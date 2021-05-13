<?php

use Brave\NeucoreApi\Api\ApplicationGroupsApi;
use Brave\PingApp\Entity\Ping;
use Brave\PingApp\Repository\PingRepository;
use Brave\PingApp\RoleProvider;
use Brave\PingApp\Security;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use Eve\Sso\AuthenticationProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use Psr\Container\ContainerInterface;
use Slim\App;
use SlimSession\Helper;

/** @noinspection PhpIncludeInspection */
return [
    'settings' => isset($_SERVER['APP_CONFIG_FILEPATH']) ?
        require_once $_SERVER['APP_CONFIG_FILEPATH'] :
        require_once 'config.php',

    App::class => function (ContainerInterface $container) {
        return new Slim\App($container);
    },

    GenericProvider::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');

        return new GenericProvider([
            'clientId' => $settings['SSO_CLIENT_ID'],
            'clientSecret' => $settings['SSO_CLIENT_SECRET'],
            'redirectUri' => $settings['SSO_REDIRECTURI'],
            'urlAuthorize' => $settings['SSO_URL_AUTHORIZE'],
            'urlAccessToken' => $settings['SSO_URL_ACCESSTOKEN'],
            'urlResourceOwnerDetails' => '',
        ]);
    },

    AuthenticationProvider::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');

        return new AuthenticationProvider(
            $container->get(GenericProvider::class),
            explode(' ', $settings['SSO_SCOPES']),
            $settings['SSO_URL_JWKS']
        );
    },

    Helper::class => function () {
        return new Helper();
    },

    ApplicationGroupsApi::class => function (ContainerInterface $container) {
        $apiKey = base64_encode(
            $container->get('settings')['CORE_APP_ID'] .
            ':'.
            $container->get('settings')['CORE_APP_TOKEN']
        );
        $config = Brave\NeucoreApi\Configuration::getDefaultConfiguration();
        $config->setHost($container->get('settings')['CORE_URL']);
        $config->setAccessToken($apiKey);
        $config->setApiKeyPrefix('Authorization', 'Bearer');

        return new Brave\NeucoreApi\Api\ApplicationGroupsApi(null, $config);
    },

    RoleProvider::class => function (ContainerInterface $container) {
        return new RoleProvider(
            $container->get(ApplicationGroupsApi::class),
            $container->get(Helper::class)
        );
    },

    EntityManagerInterface::class => function (ContainerInterface $container) {
        $config = Setup::createAnnotationMetadataConfiguration(
            [ROOT_DIR . '/src/Entity'],
            true
        );
        return EntityManager::create(
            ['url' => $container->get('settings')['DB_URL']],
            $config
        );
    },

    PingRepository::class => function (ContainerInterface $container) {
        $em = $container->get(EntityManagerInterface::class);
        $class = $em->getMetadataFactory()->getMetadataFor(Ping::class);

        return new PingRepository($em, $class);
    },

    Security::class => function (ContainerInterface $container) {
        return new Security(
            $container->get('settings')['pingMapping'],
            $container->get(RoleProvider::class),
            $container->get(Helper::class)
        );
    },
];
