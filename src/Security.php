<?php

namespace Brave\PingApp;

use Eve\Sso\EveAuthentication;
use SlimSession\Helper;

class Security
{
    /**
     * @var RoleProvider
     */
    private $roleProvider;

    /**
     * @var Helper
     */
    private $session;

    /**
     * @var array
     */
    private $pingMapping;

    public function __construct(array $pingMapping, RoleProvider $roleProvider, Helper $session)
    {
        $this->pingMapping = $pingMapping;
        $this->roleProvider = $roleProvider;
        $this->session = $session;
    }

    public function getAllowedPingGroups(): array
    {
        $coreGroups = $this->roleProvider->getRoles();

        $pingGroups = [];
        foreach ($coreGroups as $groupName) {
            if (isset($this->pingMapping[$groupName])) {
                $pingGroups = array_merge($pingGroups, $this->pingMapping[$groupName]);
            }
        }

        return array_unique($pingGroups);
    }

    public function getAuthorizedName(): string
    {
        /* @var $eveAuth EveAuthentication */
        $eveAuth = $this->session->get('eveAuth');

        return $eveAuth ? $eveAuth->getCharacterName() : '';
    }
}
