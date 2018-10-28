<?php
namespace Brave\PingApp\Entity;

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
     * @var \DateTime
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

    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->dateTime = new \DateTime();
    }
}
