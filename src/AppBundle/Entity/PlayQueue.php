<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlayQueue
 *
 * @ORM\Table(name="play_queue", indexes={@ORM\Index(name="username", columns={"username"})})
 * @ORM\Entity
 */
class PlayQueue
{
    /**
     * @var integer
     *
     * @ORM\Column(name="current", type="integer", nullable=true)
     */
    private $current;

    /**
     * @var integer
     *
     * @ORM\Column(name="position_millis", type="bigint", nullable=true)
     */
    private $positionMillis;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="changed", type="datetime", nullable=false)
     */
    private $changed;

    /**
     * @var string
     *
     * @ORM\Column(name="changed_by", type="string", length=256, nullable=false)
     */
    private $changedBy;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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

