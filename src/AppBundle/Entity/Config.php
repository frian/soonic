<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 *
 * @ORM\Table(name="config")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ConfigRepository")
 */
class Config
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var AppBundle\Entity\Language
     *
     * @ORM\OneToOne(targetEntity="Language")
     * @ORM\JoinColumn(name="language_id", referencedColumnName="id")
     */
    private $language;

    /**
     * @var AppBundle\Entity\Theme
     *
     * @ORM\OneToOne(targetEntity="Theme")
     * @ORM\JoinColumn(name="theme_id", referencedColumnName="id")
     */
    private $theme;


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
     * Set language
     *
     * @param \AppBundle\Entity\Language $language
     *
     * @return Config
     */
    public function setLanguage(\AppBundle\Entity\Language $language = null)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return \AppBundle\Entity\Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set theme
     *
     * @param \AppBundle\Entity\Theme $theme
     *
     * @return Config
     */
    public function setTheme(\AppBundle\Entity\Theme $theme = null)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme
     *
     * @return \AppBundle\Entity\Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }
}
