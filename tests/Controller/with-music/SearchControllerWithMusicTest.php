<?php

namespace App\Tests\Controller\WithMusic;

use App\Tests\Controller\WithMusicWebTestCase;

class SearchControllerWithMusicTest extends WithMusicWebTestCase
{
    public function testSearchReturnsMatchingSongs(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/search');

        $form = $crawler->filter('#search-form')->form([
            'search[keyword]' => 'sultans',
        ]);

        $crawler = $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThanOrEqual(1, $crawler->filter('td:contains("SULTANS OF SWING")')->count());
        $this->assertSelectorExists('i.icon-plus');
    }
}
