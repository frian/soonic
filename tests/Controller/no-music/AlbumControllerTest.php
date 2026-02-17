<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class AlbumControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $url = '/album/';

        $client = static::createClient();

        $application = new Application($client->getKernel());
        $application->setAutoExit(false);

        // -- drop db
        $input = new ArrayInput(array(
            'command' => 'do:da:dr',
            '--force' => true,
            '--if-exists' => true
        ));

        $output = new NullOutput();
        $application->run($input, $output);

        // -- create db
        $input = new ArrayInput(array(
            'command' => 'do:da:cr'
        ));

        $output = new NullOutput();
        $application->run($input, $output);

        // -- create db schema
        $input = new ArrayInput(array(
            'command' => 'do:mi:mi',
            '--no-interaction' => true
        ));

        $output = new NullOutput();
        $application->run($input, $output);

        // -- load fixtures
        $input = new ArrayInput(array(
            'command' => 'do:fi:lo',
            '--no-interaction' => true
        ));

        $output = new NullOutput();
        $application->run($input, $output);


        $crawler = $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.albums-view');
        $this->assertTrue($crawler->filter(".albums-view:contains(\"no album found\")")->count() == 1);
    }

    public function testAlbumView(): void
    {
        $url = '/album/1';

        $client = static::createClient();
        $crawler = $client->request('GET', $url);

        $this->assertResponseStatusCodeSame(404);
        $this->assertTrue($crawler->filter("body:contains(\"album not found\")")->count() == 1);
    }
}
