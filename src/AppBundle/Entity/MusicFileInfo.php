<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MusicFileInfo
 *
 * @ORM\Table(name="music_file_info")
 * @ORM\Entity
 */
class MusicFileInfo
{
    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=1024, nullable=false)
     */
    private $path;

    /**
     * @var integer
     *
     * @ORM\Column(name="rating", type="integer", nullable=true)
     */
    private $rating;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=2048, nullable=true)
     */
    private $comment;

    /**
     * @var integer
     *
     * @ORM\Column(name="play_count", type="integer", nullable=true)
     */
    private $playCount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_played", type="datetime", nullable=true)
     */
    private $lastPlayed;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;


}

