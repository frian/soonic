<?php

namespace App\Tests\Controller\NoMusic;

use App\Tests\Controller\NoMusicWebTestCase;

class AlbumControllerTest extends NoMusicWebTestCase
{
    public function testIndexShowsEmptyState(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/album/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.albums-view');
        $this->assertSame(1, $crawler->filter('.albums-view:contains("no album found")')->count());
    }

    public function testAlbumViewReturnsNotFoundWhenAlbumDoesNotExist(): void
    {
        $client = static::createClient();
        $client->request('GET', '/album/1');

        $this->assertResponseStatusCodeSame(404);
    }
}
