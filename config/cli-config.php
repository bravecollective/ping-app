<?php
/**
 * Required configuration for vendor/bin/doctrine.
 */
define('ROOT_DIR', realpath(__DIR__ . '/..'));
require ROOT_DIR . '/vendor/autoload.php';
$bootstrap = new \Brave\PingApp\Bootstrap();
$em = $bootstrap->getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
$helpers = new \Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));
