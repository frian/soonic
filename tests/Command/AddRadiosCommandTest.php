<?php

namespace App\Tests\Command;

use App\Command\AddRadiosCommand;
use App\Repository\RadioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddRadiosCommandTest extends \PHPUnit\Framework\TestCase
{
    private EntityManagerInterface $entityManager;
    private RadioRepository $radioRepository;
    private ValidatorInterface $validator;
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->entityManager = $this->createStub(EntityManagerInterface::class);
        $this->radioRepository = $this->createStub(RadioRepository::class);
        $this->validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $this->tmpDir = sys_get_temp_dir().'/soonic-tests-'.uniqid('', true);
        mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tmpDir)) {
            $files = glob($this->tmpDir.'/*') ?: [];
            foreach ($files as $file) {
                @unlink($file);
            }
            @rmdir($this->tmpDir);
        }
    }

    public function testInvalidFormatOptionReturnsInvalid(): void
    {
        $command = new AddRadiosCommand($this->entityManager, $this->radioRepository, $this->validator, getcwd());
        $tester = new CommandTester($command);

        $status = $tester->execute([
            'file' => __FILE__,
            '--format' => 'xml',
        ], ['interactive' => false]);

        $this->assertSame(Command::INVALID, $status);
        $this->assertStringContainsString('Invalid --format', $tester->getDisplay());
    }

    public function testDryRunCsvDoesNotPersist(): void
    {
        $csv = $this->tmpDir.'/radios.csv';
        file_put_contents($csv, "name,streamUrl,homepageUrl\nFIP Rock,https://example.com/live,https://example.com\n");

        $radioRepository = $this->createMock(RadioRepository::class);
        $radioRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');

        $command = new AddRadiosCommand($entityManager, $radioRepository, $this->validator, getcwd());
        $tester = new CommandTester($command);

        $status = $tester->execute([
            'file' => $csv,
            '--dry-run' => true,
        ], ['interactive' => false]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('Added: 1', $tester->getDisplay());
        $this->assertStringContainsString('Dry-run mode', $tester->getDisplay());
    }

    public function testM3uDuplicateStreamIsSkipped(): void
    {
        $m3u = $this->tmpDir.'/radios.m3u';
        file_put_contents($m3u, "#EXTM3U\n#EXTINF:-1,Radio A\nhttps://example.com/live\n#EXTINF:-1,Radio B\nhttps://example.com/live\n");

        $radioRepository = $this->createMock(RadioRepository::class);
        $radioRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');

        $command = new AddRadiosCommand($entityManager, $radioRepository, $this->validator, getcwd());
        $tester = new CommandTester($command);

        $status = $tester->execute([
            'file' => $m3u,
            '--dry-run' => true,
        ], ['interactive' => false]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('Added: 1', $tester->getDisplay());
        $this->assertStringContainsString('Skipped: 1', $tester->getDisplay());
    }
}
