<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ConfigControllerTest extends WebTestCase
{
    public function testEdit(): void
    {
        $url = '/config/1/edit';

        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCodeSame(302);
        $this->assertTrue($client->getResponse() instanceof RedirectResponse);

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.settings-view');
    }
}
