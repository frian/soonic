<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlayQueueFile
 *
 * @ORM\Table(name="play_queue_file", indexes={@ORM\Index(name="play_queue_id", columns={"play_queue_id"}), @ORM\Index(name="media_file_id", columns={"media_file_id"})})
 * @ORM\Entity
 */
class PlayQueueFile
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
     * @var \AppBundle\Entity\PlayQueue
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PlayQueue")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="play_queue_id", referencedColumnName="id")
     * })
     */
    private $playQueue;

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

