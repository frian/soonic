<?php

namespace App\Helper;

use Doctrine\Persistence\ManagerRegistry;

/**
 * A helper service for theme changing.
 */
class ThemeHelper
{
    private $doctrine;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * get user theme.
     *
     * @return string $theme
     */
    public function get(): string
    {
        $config = $this->doctrine->getRepository('App\Entity\Config')->find(1);
        $theme = $config->getTheme();

        return $theme;
    }
}
