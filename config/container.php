<?php

return [
    'settings' => isset($_SERVER['APP_CONFIG_FILEPATH']) ? require_once($_SERVER['APP_CONFIG_FILEPATH']) : require_once('config.php'),

    \Slim\App::class => function (\Psr\Container\ContainerInterface $container)
    {
        return new Slim\App($container);
    },

    \League\OAuth2\Client\Provider\GenericProvider::class => function (\Psr\Container\ContainerInterface $container)
    {
        $settings = $container->get('settings');

        return new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId' => $settings['SSO_CLIENT_ID'],
            'clientSecret' => $settings['SSO_CLIENT_SECRET'],
            'redirectUri' => $settings['SSO_REDIRECTURI'],
            'urlAuthorize' => $settings['SSO_URL_AUTHORIZE'],
            'urlAccessToken' => $settings['SSO_URL_ACCESSTOKEN'],
            'urlResourceOwnerDetails' => $settings['SSO_URL_RESOURCEOWNERDETAILS'],
        ]);
    },

    \Brave\Sso\Basics\AuthenticationProvider::class => function (\Psr\Container\ContainerInterface $container)
    {
        $settings = $container->get('settings');

        return new \Brave\Sso\Basics\AuthenticationProvider(
            $container->get(\League\OAuth2\Client\Provider\GenericProvider::class),
            explode(' ', $settings['SSO_SCOPES'])
        );
    },

    \Brave\PingApp\SessionHandler::class => function (\Psr\Container\ContainerInterface $container) {
        return new \Brave\PingApp\SessionHandler($container);
    },

    \Brave\Sso\Basics\SessionHandlerInterface::class => function (\Psr\Container\ContainerInterface $container) {
        return $container->get(\Brave\PingApp\SessionHandler::class);
    },

    \Brave\NeucoreApi\Api\ApplicationApi::class => function (\Psr\Container\ContainerInterface $container) {
        $apiKey = base64_encode(
            $container->get('settings')['CORE_APP_ID'] .
            ':'.
            $container->get('settings')['CORE_APP_TOKEN']
        );
        $config = Brave\NeucoreApi\Configuration::getDefaultConfiguration();
        $config->setHost($container->get('settings')['CORE_URL']);
        $config->setApiKey('Authorization', $apiKey);
        $config->setApiKeyPrefix('Authorization', 'Bearer');

        return new Brave\NeucoreApi\Api\ApplicationApi(null, $config);
    },

    \Brave\PingApp\RoleProvider::class => function (\Psr\Container\ContainerInterface $container) {
        return new \Brave\PingApp\RoleProvider(
            $container->get(\Brave\NeucoreApi\Api\ApplicationApi::class),
            $container->get(\Brave\Sso\Basics\SessionHandlerInterface::class)
        );
    },

    \Doctrine\ORM\EntityManagerInterface::class => function (\Psr\Container\ContainerInterface $container) {
        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
            [ROOT_DIR . '/src/Entity'],
            true
        );
        $em = \Doctrine\ORM\EntityManager::create(
            ['url' => $container->get('settings')['DB_URL']],
            $config
        );

        return $em;
    },

    \Brave\PingApp\Repository\PingRepository::class => function (\Psr\Container\ContainerInterface $container) {
        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
        $class = $em->getMetadataFactory()->getMetadataFor(\Brave\PingApp\Entity\Ping::class);

        return new \Brave\PingApp\Repository\PingRepository($em, $class);
    },

    \Brave\PingApp\Security::class => function (\Psr\Container\ContainerInterface $container) {
        return new \Brave\PingApp\Security(
            $container->get('settings')['pingMapping'],
            $container->get(\Brave\PingApp\RoleProvider::class),
            $container->get(\Brave\Sso\Basics\SessionHandlerInterface::class)
        );
    },
];
