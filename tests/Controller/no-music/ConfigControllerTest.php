<?php

namespace App\Tests\Controller\NoMusic;

use App\Tests\Controller\NoMusicWebTestCase;

class ConfigControllerTest extends NoMusicWebTestCase
{
    public function testSettingsPageDisplaysForm(): void
    {
        $client = static::createClient();
        $client->request('GET', '/settings/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.settings-view');
        $this->assertSelectorExists('#settings-form');
    }

    public function testConfigEditPersistsCurrentSelectionAndReturnsJson(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/settings/');

        $form = $crawler->filter('#settings-form')->form();
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('success', $payload['status'] ?? null);
        $this->assertSame('default-dark', $payload['config']['theme'] ?? null);
        $this->assertSame('fr', $payload['config']['language'] ?? null);
    }
}
