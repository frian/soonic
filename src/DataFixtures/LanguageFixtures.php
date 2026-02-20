<?php

namespace App\DataFixtures;

use App\Entity\Language;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LanguageFixtures extends Fixture
{
    public const REFERENCE_EN = 'language_en';
    public const REFERENCE_FR = 'language_fr';
    public const REFERENCE_IT = 'language_it';

    public function load(ObjectManager $manager): void
    {
        $languages = [
            self::REFERENCE_EN => [
                'name' => 'english',
                'code' => 'en',
            ],
            self::REFERENCE_FR => [
                'name' => 'français',
                'code' => 'fr',
            ],
            self::REFERENCE_IT => [
                'name' => 'italiano',
                'code' => 'it',
            ],
        ];

        foreach ($languages as $reference => $languageData) {
            $language = new Language();
            $language->setName($languageData['name']);
            $language->setCode($languageData['code']);

            $this->addReference($reference, $language);
            $manager->persist($language);
        }

        $manager->flush();
    }
}
