<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PodcastEpisode
 *
 * @ORM\Table(name="podcast_episode", indexes={@ORM\Index(name="idx_podcast_episode_url", columns={"url"}), @ORM\Index(name="channel_id", columns={"channel_id"})})
 * @ORM\Entity
 */
class PodcastEpisode
{
    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=512, nullable=false)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=1024, nullable=true)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=512, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=4096, nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publish_date", type="datetime", nullable=true)
     */
    private $publishDate;

    /**
     * @var string
     *
     * @ORM\Column(name="duration", type="string", length=32, nullable=true)
     */
    private $duration;

    /**
     * @var integer
     *
     * @ORM\Column(name="bytes_total", type="bigint", nullable=true)
     */
    private $bytesTotal;

    /**
     * @var integer
     *
     * @ORM\Column(name="bytes_downloaded", type="bigint", nullable=true)
     */
    private $bytesDownloaded;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=32, nullable=false)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="error_message", type="string", length=512, nullable=true)
     */
    private $errorMessage;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\PodcastChannel
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PodcastChannel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="channel_id", referencedColumnName="id")
     * })
     */
    private $channel;


}

