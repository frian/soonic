<?php

namespace App\Tests\Command;

use App\Command\ResetCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class ResetCommandTest extends \PHPUnit\Framework\TestCase
{
    private string|null $previousDatabaseUrl = null;

    protected function setUp(): void
    {
        $this->previousDatabaseUrl = $_SERVER['DATABASE_URL'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->previousDatabaseUrl === null) {
            unset($_SERVER['DATABASE_URL']);
        } else {
            $_SERVER['DATABASE_URL'] = $this->previousDatabaseUrl;
        }
    }

    public function testFailsInProdEnvironment(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn('prod');

        $_SERVER['DATABASE_URL'] = 'mysql://user:pass@127.0.0.1:3306/soonic';

        $command = new ResetCommand($kernel);
        $tester = new CommandTester($command);

        $status = $tester->execute(['--force' => true], ['interactive' => false]);

        $this->assertSame(Command::FAILURE, $status);
        $this->assertStringContainsString('Refusing to run in "prod" environment', $tester->getDisplay());
    }

    public function testFailsWhenDatabaseDoesNotMatchEnvironment(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn('test');

        $_SERVER['DATABASE_URL'] = 'mysql://user:pass@127.0.0.1:3306/soonic';

        $command = new ResetCommand($kernel);
        $tester = new CommandTester($command);

        $status = $tester->execute(['--force' => true], ['interactive' => false]);

        $this->assertSame(Command::FAILURE, $status);
        $this->assertStringContainsString('must target database "soonic_test"', $tester->getDisplay());
    }
}
