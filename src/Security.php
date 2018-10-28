<?php
namespace Brave\PingApp;

use Brave\Sso\Basics\SessionHandlerInterface;

/**
 *
 */
class Security
{
    /**
     * @var RoleProvider
     */
    private $roleProvider;

    /**
     * @var SessionHandlerInterface
     */
    private $session;

    private $pingMapping;

    public function __construct(array $pingMapping, RoleProvider $roleProvider, SessionHandlerInterface $session)
    {
        $this->pingMapping = $pingMapping;
        $this->roleProvider = $roleProvider;
        $this->session = $session;
    }

    public function getAllowedPingGroups()
    {
        $coreGroups = $this->roleProvider->getRoles();

        $pingGroups = [];
        foreach ($coreGroups as $groupName) {
            if (isset($this->pingMapping[$groupName])) {
                $pingGroups = array_merge($pingGroups, $this->pingMapping[$groupName]);
            }
        }
        $pingGroups = array_unique($pingGroups);

        return $pingGroups;
    }

    /**
     * @return string
     */
    public function getAuthorizedName()
    {
        /* @var $eveAuth \Brave\Sso\Basics\EveAuthentication */
        $eveAuth = $this->session->get('eveAuth', null);

        return $eveAuth ? $eveAuth->getCharacterName() : '';
    }
}
