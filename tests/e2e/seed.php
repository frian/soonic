<?php

use App\Kernel;
use App\Tests\Support\MusicDatasetSeeder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Dotenv\Dotenv;

$projectDir = dirname(__DIR__, 2);

require $projectDir.'/vendor/autoload.php';

$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = getenv('APP_ENV') ?: 'test';

$databaseUrl = getenv('DATABASE_URL');
if ($databaseUrl !== false && $databaseUrl !== '') {
    $_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'] = $databaseUrl;
}

(new Dotenv())->bootEnv($projectDir.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], true);
$kernel->boot();

$doctrine = $kernel->getContainer()->get('doctrine');
if (!$doctrine instanceof ManagerRegistry) {
    throw new RuntimeException('Doctrine manager registry is unavailable.');
}

$entityManager = $doctrine->getManager();
if (!$entityManager instanceof EntityManagerInterface) {
    throw new RuntimeException('Doctrine entity manager is unavailable.');
}

MusicDatasetSeeder::seed($entityManager, 4);

$kernel->shutdown();
