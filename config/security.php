<?php
/**
 * Required roles (one of them) for routes.
 *
 * First route match will be used, matched by "starts-with"
 */

use Brave\PingApp\RoleProvider;

return [
    '/login' => [RoleProvider::ROLE_ANY],
    '/auth' => [RoleProvider::ROLE_ANY],
    '/ping' => ['member', 'legacy-coalition'],
];
