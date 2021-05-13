<?php
/**
 * Required configuration for vendor/bin/doctrine.
 */

use Brave\PingApp\Bootstrap;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Helper\HelperSet;

define('ROOT_DIR', realpath(__DIR__ . '/..'));
require ROOT_DIR . '/vendor/autoload.php';

$bootstrap = new Bootstrap();
$em = $bootstrap->getContainer()->get(EntityManagerInterface::class); /* @var EntityManagerInterface $em */

$helpers = new HelperSet(array(
    'db' => new ConnectionHelper($em->getConnection()),
    'em' => new EntityManagerHelper($em)
));
