<?php

namespace App\DataFixtures;

use App\Entity\Config;
use App\Entity\Language;
use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_CONFIG = 'config';

    public function load(ObjectManager $manager): void
    {
        $config = new Config();
        $config->setLanguage($this->getReference(LanguageFixtures::REFERENCE_FR, Language::class));
        $config->setTheme($this->getReference(ThemeFixtures::REFERENCE_DEFAULT_DARK, Theme::class));

        $this->addReference(self::REFERENCE_CONFIG, $config);

        $manager->persist($config);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LanguageFixtures::class,
            ThemeFixtures::class,
        ];
    }
}
