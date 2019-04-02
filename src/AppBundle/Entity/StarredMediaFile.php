<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StarredMediaFile
 *
 * @ORM\Table(name="starred_media_file", indexes={@ORM\Index(name="idx_starred_media_file_media_file_id", columns={"media_file_id"}), @ORM\Index(name="idx_starred_media_file_username", columns={"username"})})
 * @ORM\Entity
 */
class StarredMediaFile
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\MediaFile
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MediaFile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="media_file_id", referencedColumnName="id")
     * })
     */
    private $mediaFile;

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

