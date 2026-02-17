<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlbumControllerWithMusicTest extends WebTestCase
{
    public function testIndex(): void
    {
        $url = '/album/';

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.albums-view');
        $this->assertSelectorExists('.album-container');
    }

    public function testAlbumView(): void
    {
        $url = '/album/1';

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.single-album-view');
    }
}
