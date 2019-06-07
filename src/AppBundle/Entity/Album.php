<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Album
 *
 * @ORM\Table(name="album", indexes={@ORM\Index(name="idx_album_name", columns={"name"}) })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AlbumRepository")
 */
class Album
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
     * @ORM\ManyToMany(targetEntity="Artist", mappedBy="albums")
     */
    private $artists;

    /**
     * @var integer
     *
     * @ORM\Column(name="song_count", type="integer", nullable=true)
     */
    private $songCount = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="duration", type="string", length=8, nullable=true)
     */
    private $duration = '0';

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
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=1024, nullable=true)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="cover_art_path", type="string", length=1024, nullable=true)
     */
    private $coverArtPath;


    /**
     * @ORM\OneToMany(targetEntity="MediaFile", mappedBy="album")
     * @ORM\OrderBy({"trackNumber" = "ASC"})
     */
    private $songs;


    public function __toString() {
        return $this->name;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->artists = new \Doctrine\Common\Collections\ArrayCollection();
        $this->songs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set duration
     *
     * @param string $duration
     *
     * @return Album
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return string
     */
    public function getDuration()
    {
        return $this->duration;
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
     * Add artist
     *
     * @param \AppBundle\Entity\Artist $artist
     *
     * @return Album
     */
    public function addArtist(\AppBundle\Entity\Artist $artist)
    {
        $this->artists[] = $artist;

        return $this;
    }

    /**
     * Remove artist
     *
     * @param \AppBundle\Entity\Artist $artist
     */
    public function removeArtist(\AppBundle\Entity\Artist $artist)
    {
        $this->artists->removeElement($artist);
    }

    /**
     * Get artists
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArtists()
    {
        return $this->artists;
    }

    /**
     * Add song
     *
     * @param \AppBundle\Entity\MediaFile $song
     *
     * @return Album
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
}
