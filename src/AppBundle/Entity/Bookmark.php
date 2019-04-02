<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bookmark
 *
 * @ORM\Table(name="bookmark", indexes={@ORM\Index(name="idx_bookmark_media_file_id", columns={"media_file_id"}), @ORM\Index(name="idx_bookmark_username", columns={"username"})})
 * @ORM\Entity
 */
class Bookmark
{
    /**
     * @var integer
     *
     * @ORM\Column(name="position_millis", type="bigint", nullable=false)
     */
    private $positionMillis;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=1024, nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="changed", type="datetime", nullable=false)
     */
    private $changed;

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

