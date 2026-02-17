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
    public function load(ObjectManager $manager): void
    {
        $config = new Config();
        $config->setLanguage($this->getReference('language1', Language::class));
        $config->setTheme($this->getReference('theme0', Theme::class));

        // add reference for further fixtures
        $this->addReference('config', $config);

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
