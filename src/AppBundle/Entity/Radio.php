<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Radio
 *
 * @ORM\Table(name="radio")
 * @ORM\Entity
 */
class Radio {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Radio
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
     * @return Radio
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
     * @return Radio
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
}
