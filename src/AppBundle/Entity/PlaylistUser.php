<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlaylistUser
 *
 * @ORM\Table(name="playlist_user", uniqueConstraints={@ORM\UniqueConstraint(name="playlist_id", columns={"playlist_id", "username"})}, indexes={@ORM\Index(name="username", columns={"username"}), @ORM\Index(name="IDX_2D8AE12B6BBD148", columns={"playlist_id"})})
 * @ORM\Entity
 */
class PlaylistUser
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
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="username", referencedColumnName="username")
     * })
     */
    private $username;


}

