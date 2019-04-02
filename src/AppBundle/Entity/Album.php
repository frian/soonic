<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Album
 *
 * @ORM\Table(name="album", indexes={@ORM\Index(name="idx_album_name", columns={"name"}), @ORM\Index(name="idx_album_artist", columns={"artist"}), @ORM\Index(name="idx_album_play_count", columns={"play_count"}), @ORM\Index(name="idx_album_last_played", columns={"last_played"}), @ORM\Index(name="idx_album_present", columns={"present"})})
 * @ORM\Entity
 */
class Album
{
    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=1024, nullable=false)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=256, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="artist", type="string", length=256, nullable=false)
     */
    private $artist;

    /**
     * @var integer
     *
     * @ORM\Column(name="song_count", type="integer", nullable=false)
     */
    private $songCount = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="duration_seconds", type="integer", nullable=false)
     */
    private $durationSeconds = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="cover_art_path", type="string", length=1024, nullable=true)
     */
    private $coverArtPath;

    /**
     * @var integer
     *
     * @ORM\Column(name="play_count", type="integer", nullable=false)
     */
    private $playCount = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_played", type="datetime", nullable=true)
     */
    private $lastPlayed;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=2048, nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_scanned", type="datetime", nullable=false)
     */
    private $lastScanned;

    /**
     * @var boolean
     *
     * @ORM\Column(name="present", type="boolean", nullable=false)
     */
    private $present;

    /**
     * @var integer
     *
     * @ORM\Column(name="year", type="integer", nullable=true)
     */
    private $year;

    /**
     * @var string
     *
     * @ORM\Column(name="genre", type="string", length=64, nullable=true)
     */
    private $genre;

    /**
     * @var integer
     *
     * @ORM\Column(name="folder_id", type="integer", nullable=true)
     */
    private $folderId;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;


}

