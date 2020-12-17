<?php
/**
 * Required roles (one of them) for routes.
 *
 * First route match will be used, matched by "starts-with"
 */
return [
    '/login' => [\Brave\PingApp\RoleProvider::ROLE_ANY],
    '/auth' => [\Brave\PingApp\RoleProvider::ROLE_ANY],
    '/ping' => ['member', 'legacy-coalition'],
];
