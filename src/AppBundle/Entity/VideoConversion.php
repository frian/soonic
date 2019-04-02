<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VideoConversion
 *
 * @ORM\Table(name="video_conversion", indexes={@ORM\Index(name="idx_video_conversion_media_file_id", columns={"media_file_id"}), @ORM\Index(name="idx_video_conversion_status", columns={"status"})})
 * @ORM\Entity
 */
class VideoConversion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="audio_track_id", type="integer", nullable=true)
     */
    private $audioTrackId;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=64, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=64, nullable=false)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="progress_seconds", type="integer", nullable=true)
     */
    private $progressSeconds;

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
     * @var \DateTime
     *
     * @ORM\Column(name="started", type="datetime", nullable=true)
     */
    private $started;

    /**
     * @var string
     *
     * @ORM\Column(name="target_file", type="string", length=1024, nullable=true)
     */
    private $targetFile;

    /**
     * @var string
     *
     * @ORM\Column(name="log_file", type="string", length=1024, nullable=true)
     */
    private $logFile;

    /**
     * @var integer
     *
     * @ORM\Column(name="bit_rate", type="integer", nullable=true)
     */
    private $bitRate;

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


}

