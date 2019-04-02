<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Transcoding2
 *
 * @ORM\Table(name="transcoding2")
 * @ORM\Entity
 */
class Transcoding2
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="source_formats", type="string", length=256, nullable=false)
     */
    private $sourceFormats;

    /**
     * @var string
     *
     * @ORM\Column(name="target_format", type="string", length=32, nullable=false)
     */
    private $targetFormat;

    /**
     * @var string
     *
     * @ORM\Column(name="step1", type="string", length=1024, nullable=false)
     */
    private $step1;

    /**
     * @var string
     *
     * @ORM\Column(name="step2", type="string", length=1024, nullable=true)
     */
    private $step2;

    /**
     * @var string
     *
     * @ORM\Column(name="step3", type="string", length=1024, nullable=true)
     */
    private $step3;

    /**
     * @var boolean
     *
     * @ORM\Column(name="default_active", type="boolean", nullable=false)
     */
    private $defaultActive = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Player", mappedBy="transcoding")
     */
    private $player;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->player = new \Doctrine\Common\Collections\ArrayCollection();
    }

}

