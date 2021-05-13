<?php

use Brave\PingApp\Bootstrap;

require_once __DIR__ . '/../vendor/autoload.php';

define('ROOT_DIR', realpath(__DIR__ . '/../'));

$bootstrap = new Bootstrap();
$app = $bootstrap->enableRoutes();
$app->run();
