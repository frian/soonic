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
            'search[keyword]' => 'dire',
        ]);

        $crawler = $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('td:contains("no songs found")');
        $this->assertSelectorExists('i.icon-plus');
    }
}
