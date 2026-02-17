<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LibraryControllerTest extends WebTestCase
{
    public function testLibrary(): void
    {
        $url = '/';

        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.topbar');
        $this->assertSelectorExists('#songsSection');
        $this->assertTrue($crawler->filter("#artists-nav:contains(\"no artists found\")")->count() == 1);
    }

    public function testShowArtistAlbums(): void
    {
        $url = '/albums/abba';

        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        $this->assertResponseStatusCodeSame(404);
        $this->assertSelectorExists('#album-nav');
        $this->assertTrue($crawler->filter("#album-nav:contains(\"no albums found\")")->count() == 1);
    }

    public function testShowAlbumsSongs(): void
    {
        $url = '/songs/abba/misc';

        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertTrue($crawler->filter("body:contains(\"no songs found\")")->count() == 1);
    }

    public function testFilterArtist(): void
    {
        $url = '/artist/filter/';

        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('#artists-nav');
        $this->assertTrue($crawler->filter("#artists-nav:contains(\"no artists found\")")->count() == 1);
    }

    public function testFilterArtistWithParam(): void
    {
        $url = '/artist/filter/abba';

        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('#artists-nav');
        $this->assertTrue($crawler->filter("#artists-nav:contains(\"no artists found\")")->count() == 1);
    }

    public function testRandomSongs(): void
    {
        $url = '/songs/random';

        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertTrue($crawler->filter("body:contains(\"no songs found\")")->count() == 1);
    }
}
