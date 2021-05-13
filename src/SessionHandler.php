<?php

namespace Brave\PingApp;

use Brave\Sso\Basics\SessionHandlerInterface;
use Psr\Container\ContainerInterface;
use SlimSession\Helper;

class SessionHandler extends Helper implements SessionHandlerInterface
{
    public function __construct(ContainerInterface $container)
    {
    }
}
