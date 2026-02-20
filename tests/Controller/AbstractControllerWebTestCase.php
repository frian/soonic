<?php

namespace App\Tests\Controller;

use App\Entity\Album;
use App\Entity\Artist;
use App\Entity\Song;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class AbstractControllerWebTestCase extends WebTestCase
{
    /** @var array<string, bool> */
    private static array $preparedModes = [];

    /** @var array<string, string> */
    private static array $prepareErrors = [];

    protected function setUp(): void
    {
        parent::setUp();

        $mode = static::seedMode();
        if (!isset(self::$preparedModes[$mode]) && !isset(self::$prepareErrors[$mode])) {
            try {
                static::rebuildDatabase();
                self::$preparedModes[$mode] = true;
            } catch (\Throwable $exception) {
                self::$prepareErrors[$mode] = $exception->getMessage();
            }
        }

        if (isset(self::$prepareErrors[$mode])) {
            self::fail('Unable to prepare test database for mode "'.$mode.'": '.self::$prepareErrors[$mode]);
        }
    }

    abstract protected static function seedMode(): string;

    private static function rebuildDatabase(): void
    {
        self::ensureKernelShutdown();
        $kernel = self::bootKernel();
        self::assertSafeTestDatabase();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        self::runCommand($application, 'doctrine:database:drop', [
            '--if-exists' => true,
            '--force' => true,
        ]);

        self::runCommand($application, 'doctrine:database:create');
        self::runCommand($application, 'doctrine:schema:create');
        self::runCommand($application, 'doctrine:fixtures:load', ['--no-interaction' => true]);

        if (static::seedMode() === 'with-music') {
            static::seedMusicDataset();
        }

        self::ensureKernelShutdown();
    }

    private static function assertSafeTestDatabase(): void
    {
        $databaseUrl = (string) (
            $_SERVER['DATABASE_URL']
            ?? $_ENV['DATABASE_URL']
            ?? getenv('DATABASE_URL')
            ?? ''
        );

        if ($databaseUrl === '') {
            throw new \RuntimeException('DATABASE_URL is empty in test environment.');
        }

        $databaseName = '';
        if (str_starts_with($databaseUrl, 'sqlite:')) {
            $databaseName = $databaseUrl;
        } else {
            $parsed = parse_url($databaseUrl);
            $databaseName = isset($parsed['path']) ? ltrim((string) $parsed['path'], '/') : '';
        }

        if ($databaseName === '' || !preg_match('/(^|[_-])test($|[_-])/i', $databaseName)) {
            throw new \RuntimeException(sprintf(
                'Refusing to run destructive test DB bootstrap on "%s". Use a dedicated *_test database in .env.test.local.',
                $databaseName === '' ? '<unknown>' : $databaseName
            ));
        }
    }

    private static function runCommand(Application $application, string $command, array $arguments = []): void
    {
        $input = new ArrayInput(array_merge(['command' => $command], $arguments));
        $buffer = new BufferedOutput();
        $statusCode = $application->run($input, $buffer);

        if ($statusCode !== 0) {
            throw new \RuntimeException(sprintf(
                "Command \"%s\" failed in test bootstrap (exit %d).\nOutput:\n%s",
                $command,
                $statusCode,
                trim($buffer->fetch())
            ));
        }
    }

    private static function seedMusicDataset(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();

        $artist = (new Artist())
            ->setName('DIRE STRAITS')
            ->setArtistSlug('dire-straits')
            ->setAlbumCount(1)
            ->setCoverArtPath(null);

        $album = (new Album())
            ->setName('Dire Straits')
            ->setAlbumSlug('dire-straits')
            ->setSongCount(25)
            ->setDuration('59:59')
            ->setYear(1978)
            ->setGenre('Rock')
            ->setPath('/music/dire-straits')
            ->setCoverArtPath(null);

        $album->addArtist($artist);

        $entityManager->persist($artist);
        $entityManager->persist($album);

        for ($i = 1; $i <= 25; ++$i) {
            $title = $i === 1 ? 'SULTANS OF SWING' : sprintf('DIRE STRAITS TRACK %02d', $i);
            $song = (new Song())
                ->setPath(sprintf('/tmp/dire-straits/%02d.mp3', $i))
                ->setWebPath(sprintf('/music/dire-straits/%02d.mp3', $i))
                ->setTitle($title)
                ->setTrackNumber($i)
                ->setYear(1978)
                ->setGenre('Rock')
                ->setDuration('03:30')
                ->setArtist($artist)
                ->setAlbum($album);

            $entityManager->persist($song);
        }

        $entityManager->flush();
    }
}
