<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Album
 *
 * @ORM\Table(name="album", indexes={@ORM\Index(name="idx_album_name", columns={"name"}), @ORM\Index(name="idx_album_artist", columns={"artist"}), @ORM\Index(name="idx_album_play_count", columns={"play_count"}), @ORM\Index(name="idx_album_last_played", columns={"last_played"}), @ORM\Index(name="idx_album_present", columns={"present"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AlbumRepository")
 */
class Album
{
    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=1024, nullable=true)
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
     * @ORM\Column(name="song_count", type="integer", nullable=true)
     */
    private $songCount = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="duration_seconds", type="integer", nullable=true)
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
     * @ORM\Column(name="play_count", type="integer", nullable=true)
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
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_scanned", type="datetime", nullable=true)
     */
    private $lastScanned;

    /**
     * @var boolean
     *
     * @ORM\Column(name="present", type="boolean", nullable=true)
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



    /**
     * Set path
     *
     * @param string $path
     *
     * @return Album
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
     * Set name
     *
     * @param string $name
     *
     * @return Album
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set artist
     *
     * @param string $artist
     *
     * @return Album
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
     * Set songCount
     *
     * @param integer $songCount
     *
     * @return Album
     */
    public function setSongCount($songCount)
    {
        $this->songCount = $songCount;

        return $this;
    }

    /**
     * Get songCount
     *
     * @return integer
     */
    public function getSongCount()
    {
        return $this->songCount;
    }

    /**
     * Set durationSeconds
     *
     * @param integer $durationSeconds
     *
     * @return Album
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
     * Set coverArtPath
     *
     * @param string $coverArtPath
     *
     * @return Album
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
     * Set playCount
     *
     * @param integer $playCount
     *
     * @return Album
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
     * @return Album
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
     * @return Album
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
     * @return Album
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
     * Set lastScanned
     *
     * @param \DateTime $lastScanned
     *
     * @return Album
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
     * Set present
     *
     * @param boolean $present
     *
     * @return Album
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
     * Set year
     *
     * @param integer $year
     *
     * @return Album
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
     * @return Album
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
     * Set folderId
     *
     * @param integer $folderId
     *
     * @return Album
     */
    public function setFolderId($folderId)
    {
        $this->folderId = $folderId;

        return $this;
    }

    /**
     * Get folderId
     *
     * @return integer
     */
    public function getFolderId()
    {
        return $this->folderId;
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
