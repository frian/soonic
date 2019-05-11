<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MediaFile
 *
 * @ORM\Table(name="media_file", indexes={@ORM\Index(name="idx_media_file_path", columns={"path"}), @ORM\Index(name="idx_media_file_parent_path", columns={"parent_path"}), @ORM\Index(name="idx_media_file_type", columns={"type"}), @ORM\Index(name="idx_media_file_album", columns={"album"}), @ORM\Index(name="idx_media_file_artist", columns={"artist"}), @ORM\Index(name="idx_media_file_album_artist", columns={"album_artist"}), @ORM\Index(name="idx_media_file_present", columns={"present"}), @ORM\Index(name="idx_media_file_genre", columns={"genre"}), @ORM\Index(name="idx_media_file_play_count", columns={"play_count"}), @ORM\Index(name="idx_media_file_created", columns={"created"}), @ORM\Index(name="idx_media_file_last_played", columns={"last_played"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MediaFileRepository")
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
     * @ORM\Column(name="web_path", type="string", length=1024, nullable=false)
     */
    private $webPath;

    /**
     * @var string
     *
     * @ORM\Column(name="folder", type="string", length=512, nullable=true)
     */
    private $folder;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=32, nullable=true)
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
     * @ORM\Column(name="variable_bit_rate", type="boolean", nullable=true)
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
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=2048, nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="changed", type="datetime", nullable=true)
     */
    private $changed;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_scanned", type="datetime", nullable=true)
     */
    private $lastScanned;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="children_last_updated", type="datetime", nullable=true)
     */
    private $childrenLastUpdated;

    /**
     * @var boolean
     *
     * @ORM\Column(name="present", type="boolean", nullable=true)
     */
    private $present;

    /**
     * @var integer
     *
     * @ORM\Column(name="version", type="integer", nullable=true)
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



    /**
     * Set path
     *
     * @param string $path
     *
     * @return MediaFile
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set webPath
     *
     * @param string $webPath
     *
     * @return MediaFile
     */
    public function setWebPath($webPath)
    {
        $this->webPath = $webPath;

        return $this;
    }

    /**
     * Get webPath
     *
     * @return string
     */
    public function getWebPath()
    {
        return $this->webPath;
    }

    /**
     * Set folder
     *
     * @param string $folder
     *
     * @return MediaFile
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get folder
     *
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return MediaFile
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set format
     *
     * @param string $format
     *
     * @return MediaFile
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return MediaFile
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set album
     *
     * @param string $album
     *
     * @return MediaFile
     */
    public function setAlbum($album)
    {
        $this->album = $album;

        return $this;
    }

    /**
     * Get album
     *
     * @return string
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * Set artist
     *
     * @param string $artist
     *
     * @return MediaFile
     */
    public function setArtist($artist)
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * Get artist
     *
     * @return string
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * Set albumArtist
     *
     * @param string $albumArtist
     *
     * @return MediaFile
     */
    public function setAlbumArtist($albumArtist)
    {
        $this->albumArtist = $albumArtist;

        return $this;
    }

    /**
     * Get albumArtist
     *
     * @return string
     */
    public function getAlbumArtist()
    {
        return $this->albumArtist;
    }

    /**
     * Set discNumber
     *
     * @param integer $discNumber
     *
     * @return MediaFile
     */
    public function setDiscNumber($discNumber)
    {
        $this->discNumber = $discNumber;

        return $this;
    }

    /**
     * Get discNumber
     *
     * @return integer
     */
    public function getDiscNumber()
    {
        return $this->discNumber;
    }

    /**
     * Set trackNumber
     *
     * @param integer $trackNumber
     *
     * @return MediaFile
     */
    public function setTrackNumber($trackNumber)
    {
        $this->trackNumber = $trackNumber;

        return $this;
    }

    /**
     * Get trackNumber
     *
     * @return integer
     */
    public function getTrackNumber()
    {
        return $this->trackNumber;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return MediaFile
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set genre
     *
     * @param string $genre
     *
     * @return MediaFile
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Get genre
     *
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set bitRate
     *
     * @param integer $bitRate
     *
     * @return MediaFile
     */
    public function setBitRate($bitRate)
    {
        $this->bitRate = $bitRate;

        return $this;
    }

    /**
     * Get bitRate
     *
     * @return integer
     */
    public function getBitRate()
    {
        return $this->bitRate;
    }

    /**
     * Set variableBitRate
     *
     * @param boolean $variableBitRate
     *
     * @return MediaFile
     */
    public function setVariableBitRate($variableBitRate)
    {
        $this->variableBitRate = $variableBitRate;

        return $this;
    }

    /**
     * Get variableBitRate
     *
     * @return boolean
     */
    public function getVariableBitRate()
    {
        return $this->variableBitRate;
    }

    /**
     * Set durationSeconds
     *
     * @param integer $durationSeconds
     *
     * @return MediaFile
     */
    public function setDurationSeconds($durationSeconds)
    {
        $this->durationSeconds = $durationSeconds;

        return $this;
    }

    /**
     * Get durationSeconds
     *
     * @return integer
     */
    public function getDurationSeconds()
    {
        return $this->durationSeconds;
    }

    /**
     * Set fileSize
     *
     * @param integer $fileSize
     *
     * @return MediaFile
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * Get fileSize
     *
     * @return integer
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Set width
     *
     * @param integer $width
     *
     * @return MediaFile
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     *
     * @return MediaFile
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set coverArtPath
     *
     * @param string $coverArtPath
     *
     * @return MediaFile
     */
    public function setCoverArtPath($coverArtPath)
    {
        $this->coverArtPath = $coverArtPath;

        return $this;
    }

    /**
     * Get coverArtPath
     *
     * @return string
     */
    public function getCoverArtPath()
    {
        return $this->coverArtPath;
    }

    /**
     * Set parentPath
     *
     * @param string $parentPath
     *
     * @return MediaFile
     */
    public function setParentPath($parentPath)
    {
        $this->parentPath = $parentPath;

        return $this;
    }

    /**
     * Get parentPath
     *
     * @return string
     */
    public function getParentPath()
    {
        return $this->parentPath;
    }

    /**
     * Set playCount
     *
     * @param integer $playCount
     *
     * @return MediaFile
     */
    public function setPlayCount($playCount)
    {
        $this->playCount = $playCount;

        return $this;
    }

    /**
     * Get playCount
     *
     * @return integer
     */
    public function getPlayCount()
    {
        return $this->playCount;
    }

    /**
     * Set lastPlayed
     *
     * @param \DateTime $lastPlayed
     *
     * @return MediaFile
     */
    public function setLastPlayed($lastPlayed)
    {
        $this->lastPlayed = $lastPlayed;

        return $this;
    }

    /**
     * Get lastPlayed
     *
     * @return \DateTime
     */
    public function getLastPlayed()
    {
        return $this->lastPlayed;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return MediaFile
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return MediaFile
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set changed
     *
     * @param \DateTime $changed
     *
     * @return MediaFile
     */
    public function setChanged($changed)
    {
        $this->changed = $changed;

        return $this;
    }

    /**
     * Get changed
     *
     * @return \DateTime
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * Set lastScanned
     *
     * @param \DateTime $lastScanned
     *
     * @return MediaFile
     */
    public function setLastScanned($lastScanned)
    {
        $this->lastScanned = $lastScanned;

        return $this;
    }

    /**
     * Get lastScanned
     *
     * @return \DateTime
     */
    public function getLastScanned()
    {
        return $this->lastScanned;
    }

    /**
     * Set childrenLastUpdated
     *
     * @param \DateTime $childrenLastUpdated
     *
     * @return MediaFile
     */
    public function setChildrenLastUpdated($childrenLastUpdated)
    {
        $this->childrenLastUpdated = $childrenLastUpdated;

        return $this;
    }

    /**
     * Get childrenLastUpdated
     *
     * @return \DateTime
     */
    public function getChildrenLastUpdated()
    {
        return $this->childrenLastUpdated;
    }

    /**
     * Set present
     *
     * @param boolean $present
     *
     * @return MediaFile
     */
    public function setPresent($present)
    {
        $this->present = $present;

        return $this;
    }

    /**
     * Get present
     *
     * @return boolean
     */
    public function getPresent()
    {
        return $this->present;
    }

    /**
     * Set version
     *
     * @param integer $version
     *
     * @return MediaFile
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
