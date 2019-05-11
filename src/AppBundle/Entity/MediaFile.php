<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MediaFile
 *
 * @ORM\Table(name="media_file", indexes={@ORM\Index(name="idx_media_file_path", columns={"path"}), @ORM\Index(name="idx_media_file_album", columns={"album"}), @ORM\Index(name="idx_media_file_artist", columns={"artist"}), @ORM\Index(name="idx_media_file_genre", columns={"genre"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MediaFileRepository")
 */
class MediaFile
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
