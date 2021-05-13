<?php

namespace Brave\PingApp\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="pings")
 */
class Ping
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    private $id;

    /**
     * @Column(type="datetime", name="date_time")
     * @var DateTime
     */
    public $dateTime;

    /**
     * @Column(type="string", name="ping_group", length=255)
     * @var string
     */
    public $group;

    /**
     * @Column(type="text", name="ping_text")
     * @var string
     */
    public $text;

    /**
     * @Column(type="string", name="character_name", length=255)
     * @var string
     */
    public $character;

    public function getId(): int
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->dateTime = new DateTime();
    }
}
