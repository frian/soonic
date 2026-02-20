<?php

namespace App\Tests\Controller\NoMusic;

use App\Tests\Controller\NoMusicWebTestCase;

class ScanControllerTest extends NoMusicWebTestCase
{
    public function testScanProgressReturnsExpectedShape(): void
    {
        $client = static::createClient();
        $client->request('GET', '/scan/progress');

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('status', $payload);
        $this->assertContains($payload['status'], ['running', 'stopped']);
        $this->assertArrayHasKey('data', $payload);
        $this->assertArrayHasKey('song', $payload['data']);
        $this->assertArrayHasKey('artist', $payload['data']);
        $this->assertArrayHasKey('album', $payload['data']);
    }

    public function testScanRouteAcceptsPostAndReturnsJsonStatus(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/settings/');
        $csrfToken = (string) $crawler->filter('#scan-button')->attr('data-csrf-token');
        $this->assertNotSame('', $csrfToken);

        $client->request('POST', '/scan/', [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_X_CSRF_TOKEN' => $csrfToken,
        ]);

        $this->assertResponseFormatSame('json');

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('status', $payload);
        $this->assertContains($payload['status'], ['started', 'already_running', 'error']);
    }
}
