<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlaylistFile
 *
 * @ORM\Table(name="playlist_file", indexes={@ORM\Index(name="playlist_id", columns={"playlist_id"}), @ORM\Index(name="media_file_id", columns={"media_file_id"})})
 * @ORM\Entity
 */
class PlaylistFile
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
     * @var \AppBundle\Entity\Playlist
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Playlist")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="playlist_id", referencedColumnName="id")
     * })
     */
    private $playlist;

    /**
     * @var \AppBundle\Entity\MediaFile
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MediaFile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="media_file_id", referencedColumnName="id")
     * })
     */
    private $mediaFile;


}

