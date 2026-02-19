<?php

namespace App\Helper;

use App\Repository\ConfigRepository;

/**
 * Provides current theme name for Twig templates.
 */
class ThemeHelper
{
    public function __construct(
        private ConfigRepository $configRepository
    )
    {
    }

    /**
     * Returns configured theme name or default fallback.
     */
    public function get(): string
    {
        $config = $this->configRepository->find(1);

        return $config?->getTheme()?->getName() ?? 'default-clear';
    }
}
