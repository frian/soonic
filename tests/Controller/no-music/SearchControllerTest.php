<?php

namespace App\Tests\Controller\NoMusic;

use App\Tests\Controller\NoMusicWebTestCase;

class SearchControllerTest extends NoMusicWebTestCase
{
    public function testSearchFormIsReachable(): void
    {
        $client = static::createClient();
        $client->request('GET', '/search');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#search-form');
        $this->assertSelectorExists('input[name="search[keyword]"]');
    }

    public function testSearchReturnsEmptyStateWhenNoSongMatches(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/search');

        $form = $crawler->filter('#search-form')->form([
            'search[keyword]' => 'dire',
        ]);

        $crawler = $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('body:contains("no songs found")')->count());
    }

    public function testSearchWithTooShortKeywordReturnsForm(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/search');

        $form = $crawler->filter('#search-form')->form([
            'search[keyword]' => 'ab',
        ]);

        $crawler = $client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorExists('#search-form');
        $this->assertSelectorNotExists('i.icon-plus');
    }

    public function testSearchWithTrimmedEmptyKeywordReturnsForm(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/search');

        $form = $crawler->filter('#search-form')->form([
            'search[keyword]' => '   ',
        ]);

        $crawler = $client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorExists('#search-form');
        $this->assertSelectorNotExists('i.icon-plus');
    }
}
