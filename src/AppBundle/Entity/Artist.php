<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Artist
 *
 * @ORM\Table(name="artist", indexes={@ORM\Index(name="idx_artist_name", columns={"name"}), @ORM\Index(name="idx_artist_present", columns={"present"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArtistRepository")
 */
class Artist
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=256, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="cover_art_path", type="string", length=1024, nullable=true)
     */
    private $coverArtPath;

    /**
     * @var integer
     *
     * @ORM\Column(name="album_count", type="integer", nullable=true)
     */
    private $albumCount = '0';

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
     * Set name
     *
     * @param string $name
     *
     * @return Artist
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
     * Set coverArtPath
     *
     * @param string $coverArtPath
     *
     * @return Artist
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
     * Set albumCount
     *
     * @param integer $albumCount
     *
     * @return Artist
     */
    public function setAlbumCount($albumCount)
    {
        $this->albumCount = $albumCount;

        return $this;
    }

    /**
     * Get albumCount
     *
     * @return integer
     */
    public function getAlbumCount()
    {
        return $this->albumCount;
    }

    /**
     * Set lastScanned
     *
     * @param \DateTime $lastScanned
     *
     * @return Artist
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
     * @return Artist
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
     * Set folderId
     *
     * @param integer $folderId
     *
     * @return Artist
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
