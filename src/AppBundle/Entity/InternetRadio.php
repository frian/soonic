<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\Type(type="\DateTime")
     *
     * @ORM\Column(name="changed", type="datetime", nullable=false)
     */
    private $changed;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function __construct(){
        $this->changed = (new \DateTime('1970-01-01 00:00:00'));
    }


    /**
     * Set name
     *
     * @param string $name
     *
     * @return InternetRadio
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set streamUrl
     *
     * @param string $streamUrl
     *
     * @return InternetRadio
     */
    public function setStreamUrl($streamUrl)
    {
        $this->streamUrl = $streamUrl;

        return $this;
    }

    /**
     * Get streamUrl
     *
     * @return string
     */
    public function getStreamUrl()
    {
        return $this->streamUrl;
    }

    /**
     * Set homepageUrl
     *
     * @param string $homepageUrl
     *
     * @return InternetRadio
     */
    public function setHomepageUrl($homepageUrl)
    {
        $this->homepageUrl = $homepageUrl;

        return $this;
    }

    /**
     * Get homepageUrl
     *
     * @return string
     */
    public function getHomepageUrl()
    {
        return $this->homepageUrl;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return InternetRadio
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set changed
     *
     * @param \DateTime $changed
     *
     * @return InternetRadio
     */
    public function setChanged($changed)
    {
        $this->changed = $changed;

        return $this;
    }

    /**
     * Get changed
     *
     * @return \DateTime
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
