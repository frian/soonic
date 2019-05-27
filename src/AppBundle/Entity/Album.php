<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Album
 *
 * @ORM\Table(name="album", indexes={@ORM\Index(name="idx_album_name", columns={"name"}), @ORM\Index(name="idx_album_artist", columns={"artist"}) })
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

}
