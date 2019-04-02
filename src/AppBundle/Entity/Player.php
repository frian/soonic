<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Player
 *
 * @ORM\Table(name="player")
 * @ORM\Entity
 */
class Player
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=512, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=64, nullable=true)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string", length=64, nullable=true)
     */
    private $ipAddress;

    /**
     * @var boolean
     *
     * @ORM\Column(name="auto_control_enabled", type="boolean", nullable=false)
     */
    private $autoControlEnabled;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_seen", type="datetime", nullable=true)
     */
    private $lastSeen;

    /**
     * @var string
     *
     * @ORM\Column(name="cover_art_scheme", type="string", length=32, nullable=false)
     */
    private $coverArtScheme;

    /**
     * @var string
     *
     * @ORM\Column(name="transcode_scheme", type="string", length=32, nullable=false)
     */
    private $transcodeScheme;

    /**
     * @var boolean
     *
     * @ORM\Column(name="dynamic_ip", type="boolean", nullable=false)
     */
    private $dynamicIp = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="client_side_playlist", type="boolean", nullable=false)
     */
    private $clientSidePlaylist = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="jukebox", type="boolean", nullable=false)
     */
    private $jukebox = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="technology", type="string", length=32, nullable=false)
     */
    private $technology = 'WEB';

    /**
     * @var string
     *
     * @ORM\Column(name="client_id", type="string", length=64, nullable=true)
     */
    private $clientId;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Transcoding2", inversedBy="player")
     * @ORM\JoinTable(name="player_transcoding2",
     *   joinColumns={
     *     @ORM\JoinColumn(name="player_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="transcoding_id", referencedColumnName="id")
     *   }
     * )
     */
    private $transcoding;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->transcoding = new \Doctrine\Common\Collections\ArrayCollection();
    }

}

