<?php

namespace App\DataFixtures;

use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ThemeFixtures extends Fixture
{
    public const REFERENCE_DEFAULT_DARK = 'theme_default_dark';
    public const REFERENCE_DEFAULT_CLEAR = 'theme_default_clear';

    public function load(ObjectManager $manager): void
    {
        $themes = [
            self::REFERENCE_DEFAULT_DARK => [
                'name' => 'default-dark',
            ],
            self::REFERENCE_DEFAULT_CLEAR => [
                'name' => 'default-clear',
            ],
        ];

        foreach ($themes as $reference => $themeData) {
            $theme = new Theme();
            $theme->setName($themeData['name']);

            $this->addReference($reference, $theme);
            $manager->persist($theme);
        }

        $manager->flush();
    }
}
