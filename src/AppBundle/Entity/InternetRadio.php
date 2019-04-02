<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InternetRadio
 *
 * @ORM\Table(name="internet_radio")
 * @ORM\Entity
 */
class InternetRadio
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=256, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="stream_url", type="string", length=512, nullable=false)
     */
    private $streamUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="homepage_url", type="string", length=512, nullable=true)
     */
    private $homepageUrl;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="changed", type="datetime", nullable=false)
     */
    private $changed = '1970-01-01 00:00:00';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;


}

