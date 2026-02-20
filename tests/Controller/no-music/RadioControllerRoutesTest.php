<?php

namespace App\Tests\Controller\NoMusic;

use App\Entity\Radio;
use App\Tests\Controller\NoMusicWebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;

class RadioControllerRoutesTest extends NoMusicWebTestCase
{
    public function testShowRouteDisplaysRadio(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/radio/new');

        $form = $crawler->filter('form[name="radio"]')->form([
            'radio[name]' => 'Radio Show',
            'radio[streamUrl]' => 'https://example.com/show',
            'radio[homepageUrl]' => 'https://example.com',
        ]);
        $client->submit($form);

        /** @var Radio|null $radio */
        $radio = static::getContainer()->get('doctrine')->getRepository(Radio::class)->findOneBy(['name' => 'Radio Show']);
        $this->assertNotNull($radio);

        $client->request('GET', sprintf('/radio/%d', $radio->getId()));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Radio Show');
    }

    public function testEditRouteGetAndPostSuccess(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/radio/new');

        $form = $crawler->filter('form[name="radio"]')->form([
            'radio[name]' => 'Radio Edit',
            'radio[streamUrl]' => 'https://example.com/edit',
            'radio[homepageUrl]' => 'https://example.com',
        ]);
        $client->submit($form);

        /** @var Radio|null $radio */
        $radio = static::getContainer()->get('doctrine')->getRepository(Radio::class)->findOneBy(['name' => 'Radio Edit']);
        $this->assertNotNull($radio);

        $crawler = $client->request('GET', sprintf('/radio/%d/edit', $radio->getId()));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form#radio_save');

        $editForm = $crawler->filter('form#radio_save')->form([
            'radio[name]' => 'Radio Edited',
            'radio[streamUrl]' => 'https://example.com/edited',
            'radio[homepageUrl]' => 'https://example.com/home',
        ]);
        $client->submit($editForm);

        $this->assertResponseRedirects('/radio/', 303);

        /** @var Radio|null $edited */
        $edited = static::getContainer()->get('doctrine')->getRepository(Radio::class)->find($radio->getId());
        $this->assertNotNull($edited);
        $this->assertSame('Radio Edited', $edited->getName());
        $this->assertSame('https://example.com/edited', $edited->getStreamUrl());
    }

    public function testDeleteWithValidCsrfRemovesRadio(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/radio/new');

        $form = $crawler->filter('form[name="radio"]')->form([
            'radio[name]' => 'Radio Remove',
            'radio[streamUrl]' => 'https://example.com/remove',
            'radio[homepageUrl]' => 'https://example.com',
        ]);
        $client->submit($form);

        /** @var Radio|null $radio */
        $radio = static::getContainer()->get('doctrine')->getRepository(Radio::class)->findOneBy(['name' => 'Radio Remove']);
        $this->assertNotNull($radio);

        $session = static::getContainer()->get('session.factory')->createSession();
        $session->start();

        $request = new Request();
        $request->setSession($session);
        $requestStack = static::getContainer()->get('request_stack');
        $requestStack->push($request);

        $token = static::getContainer()->get('security.csrf.token_manager')->getToken('delete'.$radio->getId())->getValue();
        $requestStack->pop();
        $session->save();

        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        $client->request(
            'DELETE',
            sprintf('/radio/%d', $radio->getId()),
            ['_token' => $token],
            [],
            [
                'HTTP_ORIGIN' => 'http://localhost',
                'HTTP_REFERER' => sprintf('http://localhost/radio/%d', $radio->getId()),
                'HTTP_X_CSRF_TOKEN' => $token,
            ]
        );
        $this->assertResponseRedirects('/radio/', 303);

        $deleted = static::getContainer()->get('doctrine')->getRepository(Radio::class)->find($radio->getId());
        $this->assertNull($deleted);
    }
}
