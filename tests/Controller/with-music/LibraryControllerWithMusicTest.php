<?php

namespace App\Tests\Controller\WithMusic;

use App\Tests\Controller\WithMusicWebTestCase;

class LibraryControllerWithMusicTest extends WithMusicWebTestCase
{
    public function testLibrary(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.topbar');
        $this->assertSelectorExists('#songs-section');
        $this->assertSame(1, $crawler->filter('.artist:contains("DIRE STRAITS")')->count());
    }

    public function testShowArtistAlbums(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/albums/dire-straits');

        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('#album-nav .song:contains("Dire Straits")')->count());
    }

    public function testShowAlbumsSongs(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/songs/dire-straits/dire-straits');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('td:contains("no songs found")');
        $this->assertGreaterThanOrEqual(1, $crawler->filter('i.icon-plus')->count());
    }

    public function testFilterArtist(): void
    {
        $client = static::createClient();
        $client->request('GET', '/artist/filter/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#artists-nav');
        $this->assertSelectorTextSame('a.artist', 'DIRE STRAITS');
    }

    public function testFilterArtistWithParam(): void
    {
        $client = static::createClient();
        $client->request('GET', '/artist/filter/dire');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#artists-nav');
        $this->assertSelectorTextSame('a.artist', 'DIRE STRAITS');
    }

    public function testRandomSongs(): void
    {
        $client = static::createClient();
        $client->request('GET', '/songs/random');

        $this->assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        $this->assertStringNotContainsString('no songs found', $content);

        preg_match_all('/<tr[^>]*\bdata-path=/', $content, $rows);
        $rowCount = count($rows[0]);
        $this->assertGreaterThanOrEqual(1, $rowCount);
        $this->assertLessThanOrEqual(20, $rowCount);

        preg_match_all('/\bdata-path="([^"]*)"/', $content, $matches);
        $paths = $matches[1];
        $this->assertCount($rowCount, $paths);
        $this->assertCount($rowCount, array_unique($paths));
    }
}
