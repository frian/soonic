<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MediaFile
 *
 * @ORM\Table(name="media_file", indexes={@ORM\Index(name="idx_media_file_path", columns={"path"}), @ORM\Index(name="idx_media_file_parent_path", columns={"parent_path"}), @ORM\Index(name="idx_media_file_type", columns={"type"}), @ORM\Index(name="idx_media_file_album", columns={"album"}), @ORM\Index(name="idx_media_file_artist", columns={"artist"}), @ORM\Index(name="idx_media_file_album_artist", columns={"album_artist"}), @ORM\Index(name="idx_media_file_present", columns={"present"}), @ORM\Index(name="idx_media_file_genre", columns={"genre"}), @ORM\Index(name="idx_media_file_play_count", columns={"play_count"}), @ORM\Index(name="idx_media_file_created", columns={"created"}), @ORM\Index(name="idx_media_file_last_played", columns={"last_played"})})
 * @ORM\Entity
 */
class MediaFile
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
     * @ORM\Column(name="folder", type="string", length=512, nullable=true)
     */
    private $folder;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=32, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="format", type="string", length=16, nullable=true)
     */
    private $format;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=512, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="album", type="string", length=256, nullable=true)
     */
    private $album;

    /**
     * @var string
     *
     * @ORM\Column(name="artist", type="string", length=256, nullable=true)
     */
    private $artist;

    /**
     * @var string
     *
     * @ORM\Column(name="album_artist", type="string", length=256, nullable=true)
     */
    private $albumArtist;

    /**
     * @var integer
     *
     * @ORM\Column(name="disc_number", type="integer", nullable=true)
     */
    private $discNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="track_number", type="integer", nullable=true)
     */
    private $trackNumber;

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
     * @ORM\Column(name="bit_rate", type="integer", nullable=true)
     */
    private $bitRate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="variable_bit_rate", type="boolean", nullable=false)
     */
    private $variableBitRate;

    /**
     * @var integer
     *
     * @ORM\Column(name="duration_seconds", type="integer", nullable=true)
     */
    private $durationSeconds;

    /**
     * @var integer
     *
     * @ORM\Column(name="file_size", type="bigint", nullable=true)
     */
    private $fileSize;

    /**
     * @var integer
     *
     * @ORM\Column(name="width", type="integer", nullable=true)
     */
    private $width;

    /**
     * @var integer
     *
     * @ORM\Column(name="height", type="integer", nullable=true)
     */
    private $height;

    /**
     * @var string
     *
     * @ORM\Column(name="cover_art_path", type="string", length=1024, nullable=true)
     */
    private $coverArtPath;

    /**
     * @var string
     *
     * @ORM\Column(name="parent_path", type="string", length=1024, nullable=true)
     */
    private $parentPath;

    /**
     * @var integer
     *
     * @ORM\Column(name="play_count", type="integer", nullable=false)
     */
    private $playCount;

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
     * @ORM\Column(name="changed", type="datetime", nullable=false)
     */
    private $changed;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_scanned", type="datetime", nullable=false)
     */
    private $lastScanned;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="children_last_updated", type="datetime", nullable=false)
     */
    private $childrenLastUpdated;

    /**
     * @var boolean
     *
     * @ORM\Column(name="present", type="boolean", nullable=false)
     */
    private $present;

    /**
     * @var integer
     *
     * @ORM\Column(name="version", type="integer", nullable=false)
     */
    private $version;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;


}

