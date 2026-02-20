<?php

namespace App\Tests\Controller\NoMusic;

use App\Tests\Controller\NoMusicWebTestCase;

class LibraryControllerTest extends NoMusicWebTestCase
{
    public function testLibrary(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.topbar');
        $this->assertSelectorExists('#songs-section');
        $this->assertSame(1, $crawler->filter('#artists-nav:contains("no artists found")')->count());
    }

    public function testShowArtistAlbumsReturnsNotFoundWhenArtistMissing(): void
    {
        $client = static::createClient();
        $client->request('GET', '/albums/abba');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShowAlbumsSongsReturnsNotFoundWhenArtistMissing(): void
    {
        $client = static::createClient();
        $client->request('GET', '/songs/abba/misc');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testFilterArtistWithoutParamShowsEmptyState(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/artist/filter/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#artists-nav');
        $this->assertSame(1, $crawler->filter('#artists-nav:contains("no artists found")')->count());
    }

    public function testFilterArtistWithParamShowsEmptyState(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/artist/filter/abba');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#artists-nav');
        $this->assertSame(1, $crawler->filter('#artists-nav:contains("no artists found")')->count());
    }

    public function testRandomSongsShowsEmptyState(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/songs/random');

        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('body:contains("no songs found")')->count());
    }
}
