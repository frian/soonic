<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class ScanCommandTest extends KernelTestCase
{
    private Filesystem $filesystem;
    private string $projectDir;
    private string $musicPath;
    private string $renamedMusicPath;
    private string $lockFilePath;
    private string $testSymlinkPath;
    private string $advancedTestFilesPath;
    private string $musicBackupPath;
    private bool $originalMusicPathExisted = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();
        $this->projectDir = Path::normalize(dirname(__DIR__, 2));
        $this->musicPath = Path::normalize($this->projectDir.'/public/music');
        $this->renamedMusicPath = Path::normalize($this->projectDir.'/public/music_renamed_for_tests');
        $this->lockFilePath = Path::normalize($this->projectDir.'/var/lock/soonic.lock');
        $this->testSymlinkPath = Path::normalize($this->musicPath.'/__soonic_scan_fixture_'.str_replace('.', '_', uniqid('', true)));
        $this->advancedTestFilesPath = Path::normalize($this->projectDir.'/tests/Command/testfiles');
        $this->musicBackupPath = Path::normalize($this->projectDir.'/public/music_backup_for_tests');

        // Make tests resilient to interrupted previous runs.
        if ($this->pathExistsOrIsLink($this->lockFilePath)) {
            $this->filesystem->remove($this->lockFilePath);
        }
        if ($this->pathExistsOrIsLink($this->testSymlinkPath)) {
            $this->filesystem->remove($this->testSymlinkPath);
        }

        $this->isolateMusicDirectory();
    }

    protected function tearDown(): void
    {
        if ($this->pathExistsOrIsLink($this->lockFilePath)) {
            $this->filesystem->remove($this->lockFilePath);
        }

        if ($this->pathExistsOrIsLink($this->testSymlinkPath)) {
            $this->filesystem->remove($this->testSymlinkPath);
        }

        if ($this->filesystem->exists($this->renamedMusicPath) && !$this->filesystem->exists($this->musicPath)) {
            $this->filesystem->rename($this->renamedMusicPath, $this->musicPath);
        }

        if ($this->filesystem->exists($this->musicBackupPath)) {
            if ($this->pathExistsOrIsLink($this->musicPath)) {
                $this->filesystem->remove($this->musicPath);
            }
            $this->filesystem->rename($this->musicBackupPath, $this->musicPath);
        } elseif (!$this->originalMusicPathExisted && $this->pathExistsOrIsLink($this->musicPath)) {
            // Restore the original "missing public/music" state if it did not exist.
            $this->filesystem->remove($this->musicPath);
        }

        parent::tearDown();
    }

    public function testCommandFailsWhenMusicFolderIsMissing(): void
    {
        $tester = $this->createCommandTester();
        $this->filesystem->rename($this->musicPath, $this->renamedMusicPath);

        $output = $this->executeAndGetOutput($tester);
        $this->assertStringContainsString('not found', $output);
    }

    public function testCommandWarnsWhenAlreadyRunning(): void
    {
        $tester = $this->createCommandTester();
        $this->filesystem->mkdir(dirname($this->lockFilePath));
        $this->filesystem->touch($this->lockFilePath);

        $output = $this->executeAndGetOutput($tester);
        $this->assertStringContainsString('already running', $output);
    }

    public function testCommandWorksWithoutAudioFilesForAllVerbosityLevels(): void
    {
        $this->prepareEmptyMusicDirectory();
        $tester = $this->createCommandTester();

        $output = $this->executeAndGetOutput($tester);
        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Scanning', $output);
        $this->assertStringContainsString('░', $output);
        $this->assertStringContainsString('no audio file found', $output);

        $output = $this->executeAndGetOutput($tester, OutputInterface::VERBOSITY_VERBOSE);
        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Clearing tables', $output);
        $this->assertStringContainsString('Scanning', $output);
        $this->assertStringContainsString('░', $output);
        $this->assertStringContainsString('Summary', $output);
        $this->assertStringContainsString('analysed 0 files', $output);
        $this->assertStringContainsString('no audio file found', $output);

        $output = $this->executeAndGetOutput($tester, OutputInterface::VERBOSITY_VERY_VERBOSE);
        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Clearing tables', $output);
        $this->assertStringContainsString('Scanning', $output);
        $this->assertStringNotContainsString('░', $output);
        $this->assertStringContainsString('Summary', $output);
        $this->assertStringContainsString('analysed 0 files', $output);
        $this->assertStringContainsString('no audio file found', $output);
    }

    private function prepareEmptyMusicDirectory(): void
    {
        if ($this->pathExistsOrIsLink($this->musicPath)) {
            $this->filesystem->remove($this->musicPath);
        }
        $this->filesystem->mkdir($this->musicPath);
    }

    public function testAdvancedCommandWithBugFiles(): void
    {
        $okFolder = $this->advancedTestFilesPath.'/ok';
        $bugFolder = $this->advancedTestFilesPath.'/bugs';
        if (!is_dir($okFolder) || !is_dir($bugFolder)) {
            self::markTestSkipped('Advanced fixtures not found in tests/Command/testfiles/(ok|bugs).');
        }

        $tester = $this->createCommandTester();
        if ($this->pathExistsOrIsLink($this->testSymlinkPath)) {
            $this->filesystem->remove($this->testSymlinkPath);
        }
        $this->filesystem->symlink($bugFolder, $this->testSymlinkPath);

        $output = $this->executeAndGetOutput($tester);
        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Scanning', $output);
        $this->assertStringContainsString('░', $output);
        $this->assertStringNotContainsString('no audio file found', $output);
        $this->assertStringContainsString('[WARNING] some files have missing tags', $output);
        $this->assertStringContainsString('check var/scan/soonic.log or run with -vv', $output);

        $output = $this->executeAndGetOutput($tester, OutputInterface::VERBOSITY_VERBOSE);
        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Clearing tables', $output);
        $this->assertStringContainsString('Scanning', $output);
        $this->assertStringContainsString('░', $output);
        $this->assertStringContainsString('[WARNING] some files have missing tags', $output);
        $this->assertStringContainsString('check var/scan/soonic.log or run with -vv', $output);
        $this->assertStringContainsString('Loading db', $output);
        $this->assertStringContainsString('Summary', $output);
        $this->assertStringNotContainsString('analysed 0 files', $output);
        $this->assertStringNotContainsString('no audio file found', $output);

        $output = $this->executeAndGetOutput($tester, OutputInterface::VERBOSITY_VERY_VERBOSE);
        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Clearing tables', $output);
        $this->assertStringContainsString('Scanning', $output);
        $this->assertStringNotContainsString('░', $output);
        $this->assertStringContainsString('added album', $output);
        $this->assertStringContainsString('[WARNING]', $output);
        $this->assertStringContainsString('[ERROR]', $output);
        $this->assertStringContainsString('SKIPPING FILE', $output);
        $this->assertStringContainsString('Loading db', $output);
        $this->assertStringContainsString('Summary', $output);
        $this->assertStringNotContainsString('analysed 0 files', $output);
        $this->assertStringNotContainsString('no audio file found', $output);
    }

    public function testAdvancedCommandWithOkFiles(): void
    {
        $okFolder = $this->advancedTestFilesPath.'/ok';
        $bugFolder = $this->advancedTestFilesPath.'/bugs';
        if (!is_dir($okFolder) || !is_dir($bugFolder)) {
            self::markTestSkipped('Advanced fixtures not found in tests/Command/testfiles/(ok|bugs).');
        }

        $tester = $this->createCommandTester();
        if ($this->pathExistsOrIsLink($this->testSymlinkPath)) {
            $this->filesystem->remove($this->testSymlinkPath);
        }
        $this->filesystem->symlink($okFolder, $this->testSymlinkPath);

        $output = $this->executeAndGetOutput($tester);
        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Scanning', $output);
        $this->assertStringContainsString('░', $output);
        $this->assertStringNotContainsString('no audio file found', $output);

        $output = $this->executeAndGetOutput($tester, OutputInterface::VERBOSITY_VERBOSE);
        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Clearing tables', $output);
        $this->assertStringContainsString('Scanning', $output);
        $this->assertStringContainsString('░', $output);
        $this->assertStringContainsString('Loading db', $output);
        $this->assertStringContainsString('Summary', $output);
        $this->assertStringNotContainsString('analysed 0 files', $output);
        $this->assertStringNotContainsString('no audio file found', $output);

        $output = $this->executeAndGetOutput($tester, OutputInterface::VERBOSITY_VERY_VERBOSE);
        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Clearing tables', $output);
        $this->assertStringContainsString('Scanning', $output);
        $this->assertStringNotContainsString('░', $output);
        $this->assertStringContainsString('added album', $output);
        $this->assertStringContainsString('Loading db', $output);
        $this->assertStringContainsString('Summary', $output);
        $this->assertStringNotContainsString('analysed 0 files', $output);
        $this->assertStringNotContainsString('no audio file found', $output);
    }

    private function createCommandTester(): CommandTester
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find('soonic:scan');

        return new CommandTester($command);
    }

    private function executeAndGetOutput(CommandTester $tester, int $verbosity = OutputInterface::VERBOSITY_NORMAL): string
    {
        $options = [];
        if ($verbosity !== OutputInterface::VERBOSITY_NORMAL) {
            $options['verbosity'] = $verbosity;
        }

        $tester->execute([], $options);

        return $tester->getDisplay();
    }

    private function pathExistsOrIsLink(string $path): bool
    {
        return $this->filesystem->exists($path) || is_link($path);
    }

    private function isolateMusicDirectory(): void
    {
        if ($this->pathExistsOrIsLink($this->musicBackupPath)) {
            self::fail('Temporary music backup path already exists: '.$this->musicBackupPath);
        }

        if ($this->pathExistsOrIsLink($this->renamedMusicPath)) {
            self::fail('Temporary renamed music path already exists: '.$this->renamedMusicPath);
        }

        $this->originalMusicPathExisted = $this->pathExistsOrIsLink($this->musicPath);

        if ($this->originalMusicPathExisted) {
            $this->filesystem->rename($this->musicPath, $this->musicBackupPath);
        }

        $this->filesystem->mkdir($this->musicPath);
    }

}
