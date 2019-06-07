<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Artist
 *
 * @ORM\Table(name="artist", indexes={@ORM\Index(name="idx_artist_name", columns={"name"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArtistRepository")
 */
class Artist
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=256, nullable=false)
     */
    private $name;

    /**
    * @var integer
    *
    * @ORM\Column(name="album_count", type="integer", nullable=true)
    */
    private $albumCount = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="cover_art_path", type="string", length=1024, nullable=true)
     */
    private $coverArtPath;

    /**
     * @ORM\OneToMany(targetEntity="MediaFile", mappedBy="artist")
     */
    private $songs;

    /**
     * @ORM\ManyToMany(targetEntity="Album", inversedBy="artists")
     * @ORM\JoinTable(name="albums_artists")
     */
    private $albums;


    public function __toString() {
        return $this->name;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->songs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->albums = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add song
     *
     * @param \AppBundle\Entity\MediaFile $song
     *
     * @return Artist
     */
    public function addSong(\AppBundle\Entity\MediaFile $song)
    {
        $this->songs[] = $song;

        return $this;
    }

    /**
     * Remove song
     *
     * @param \AppBundle\Entity\MediaFile $song
     */
    public function removeSong(\AppBundle\Entity\MediaFile $song)
    {
        $this->songs->removeElement($song);
    }

    /**
     * Get songs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSongs()
    {
        return $this->songs;
    }

    /**
     * Add album
     *
     * @param \AppBundle\Entity\Album $album
     *
     * @return Artist
     */
    public function addAlbum(\AppBundle\Entity\Album $album)
    {
        $this->albums[] = $album;

        return $this;
    }

    /**
     * Remove album
     *
     * @param \AppBundle\Entity\Album $album
     */
    public function removeAlbum(\AppBundle\Entity\Album $album)
    {
        $this->albums->removeElement($album);
    }

    /**
     * Get albums
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAlbums()
    {
        return $this->albums;
    }
}
