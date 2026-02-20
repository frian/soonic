<?php

namespace App\Tests\Controller\NoMusic;

use App\Tests\Controller\NoMusicWebTestCase;

class RadioControllerTest extends NoMusicWebTestCase
{
    public function testIndexShowsEmptyState(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/radio/');

        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('.radios-view:contains("no radios found")')->count());
    }

    public function testNewFormIsReachable(): void
    {
        $client = static::createClient();
        $client->request('GET', '/radio/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="radio"]');
    }
}
