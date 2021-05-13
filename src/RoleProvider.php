<?php

namespace Brave\PingApp;

use Brave\NeucoreApi\Api\ApplicationGroupsApi;
use Brave\NeucoreApi\ApiException;
use Brave\Sso\Basics\EveAuthentication;
use Brave\Sso\Basics\SessionHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tkhamez\Slim\RoleAuth\RoleProviderInterface;

/**
 * Provides groups from Brave Core from an authenticated user.
 */
class RoleProvider implements RoleProviderInterface
{
    /**
     * This role is always added.
     */
    const ROLE_ANY = 'role:any';

    /**
     * @var ApplicationGroupsApi
     */
    private $api;

    /**
     * @var SessionHandlerInterface
     */
    private $session;

    public function __construct(ApplicationGroupsApi $api, SessionHandlerInterface $session)
    {
        $this->api = $api;
        $this->session = $session;
    }

    /**
     * @return string[]
     */
    public function getRoles(ServerRequestInterface $request = null): array
    {
        $roles = [self::ROLE_ANY];
        /* @var $eveAuth EveAuthentication */
        $eveAuth = $this->session->get('eveAuth');
        if ($eveAuth === null) {
            return $roles;
        }
        // try cache
        $coreGroups = $this->session->get('coreGroups');
        if (is_array($coreGroups) && $coreGroups['time'] > (time() - 60 * 60)) {
            return $coreGroups['roles'];
        }
        // get groups from Core
        try {
            $groups = $this->api->groupsV1($eveAuth->getCharacterId());
        } catch (ApiException $ae) {
            // Don't log 404 character not found error from Core (response body is empty).
            // If the URL was not found the response body contains HTML (from Core)
            if ($ae->getCode() !== 404 || $ae->getResponseBody() !== '') {
                error_log((string)$ae);
            }

            return $roles;
        }
        foreach ($groups as $group) {
            $roles[] = $group->getName();
        }
        // cache roles
        $this->session->set('coreGroups', [
            'time' => time(),
            'roles' => $roles
        ]);

        return $roles;
    }

    public function clear()
    {
        $this->session->set('coreGroups', null);
    }
}
