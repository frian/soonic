<?php

namespace App\Tests\Controller\NoMusic;

use App\Entity\Radio;
use App\Tests\Controller\NoMusicWebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class RadioControllerTest extends NoMusicWebTestCase
{
    public function testIndexShowsEmptyState(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        foreach ($entityManager->getRepository(Radio::class)->findAll() as $radio) {
            $entityManager->remove($radio);
        }
        $entityManager->flush();

        $crawler = $client->request('GET', '/radio/');

        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('.radio-name:contains("no radios found")')->count());
    }

    public function testNewFormIsReachable(): void
    {
        $client = static::createClient();
        $client->request('GET', '/radio/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="radio"]');
    }

    public function testCreateRadioRedirectsOnSuccess(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/radio/new');

        $form = $crawler->filter('form[name="radio"]')->form([
            'radio[name]' => 'Radio Test',
            'radio[streamUrl]' => 'https://example.com/live',
            'radio[homepageUrl]' => 'https://example.com',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/radio/', 303);
    }

    public function testCreateRadioWithInvalidStreamUrlStaysOnForm(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/radio/new');

        $form = $crawler->filter('form[name="radio"]')->form([
            'radio[name]' => 'Radio Invalid Url',
            'radio[streamUrl]' => 'not-a-url',
            'radio[homepageUrl]' => 'https://example.com',
        ]);

        $crawler = $client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorExists('form[name="radio"]');
        $this->assertSelectorTextContains('h1', 'nouvelle radio');
    }

    public function testDeleteWithoutCsrfDoesNotRemoveRadio(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/radio/new');

        $form = $crawler->filter('form[name="radio"]')->form([
            'radio[name]' => 'Radio Keep',
            'radio[streamUrl]' => 'https://example.com/keep',
            'radio[homepageUrl]' => 'https://example.com',
        ]);

        $client->submit($form);
        $this->assertResponseRedirects('/radio/', 303);

        /** @var Radio|null $created */
        $created = static::getContainer()->get('doctrine')->getRepository(Radio::class)->findOneBy(['name' => 'Radio Keep']);
        $this->assertNotNull($created);

        $client->request('DELETE', sprintf('/radio/%d', $created->getId()), ['_token' => 'invalid-token']);
        $this->assertResponseRedirects('/radio/', 303);

        $stillThere = static::getContainer()->get('doctrine')->getRepository(Radio::class)->findOneBy(['id' => $created->getId()]);
        $this->assertNotNull($stillThere);
    }
}
