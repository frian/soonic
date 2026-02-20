<?php

namespace App\Tests\Controller\WithMusic;

use App\Tests\Controller\WithMusicWebTestCase;

class AlbumControllerWithMusicTest extends WithMusicWebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/album/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.albums-view');
        $this->assertSelectorExists('.album-container');
    }

    public function testAlbumView(): void
    {
        $client = static::createClient();
        $client->request('GET', '/album/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.single-album-view');
    }
}
