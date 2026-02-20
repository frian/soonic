<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'soonic:reset',
    description: 'Drop/create DB, create schema and load fixtures for dev/test environment.'
)]
class ResetCommand extends Command
{
    public function __construct(private readonly KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Run without confirmation.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $env = $this->kernel->getEnvironment();

        if (!in_array($env, ['dev', 'test'], true)) {
            $io->error(sprintf('Refusing to run in "%s" environment. Allowed: dev, test.', $env));

            return Command::FAILURE;
        }

        $expectedDbByEnv = [
            'dev' => 'soonic',
            'test' => 'soonic_test',
        ];
        $expectedDb = $expectedDbByEnv[$env];
        $configuredDb = $this->resolveDatabaseName();

        if ($configuredDb === null) {
            $io->error('Unable to resolve database name from DATABASE_URL.');

            return Command::FAILURE;
        }

        if ($configuredDb !== $expectedDb) {
            $io->error(sprintf(
                'Environment "%s" must target database "%s", current DATABASE_URL points to "%s".',
                $env,
                $expectedDb,
                $configuredDb
            ));

            return Command::FAILURE;
        }

        if (!$input->getOption('force')) {
            $confirmed = $io->confirm(
                sprintf('This will DROP and recreate database "%s" for "%s". Continue?', $configuredDb, $env),
                false
            );

            if (!$confirmed) {
                $io->warning('Reset cancelled.');

                return Command::SUCCESS;
            }
        }

        $application = $this->getApplication();
        if ($application === null) {
            $io->error('Console application is not available.');

            return Command::FAILURE;
        }
        $application->setAutoExit(false);

        $steps = [
            ['doctrine:database:drop', ['--if-exists' => true, '--force' => true]],
            ['doctrine:database:create', ['--if-not-exists' => true]],
            ['doctrine:schema:create', []],
            ['doctrine:fixtures:load', ['--no-interaction' => true]],
        ];

        foreach ($steps as [$commandName, $arguments]) {
            $io->writeln(sprintf('<info>Running %s...</info>', $commandName));

            $statusCode = $this->runSubCommand($application, $output, $commandName, $arguments);
            if ($statusCode !== Command::SUCCESS) {
                $io->error(sprintf('Step failed: %s (exit code %d).', $commandName, $statusCode));

                return Command::FAILURE;
            }
        }

        $io->success(sprintf('Database reset completed successfully for "%s".', $env));

        return Command::SUCCESS;
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function runSubCommand(\Symfony\Component\Console\Application $application, OutputInterface $output, string $commandName, array $arguments): int
    {
        $subInput = new ArrayInput(array_merge(['command' => $commandName], $arguments));
        $subInput->setInteractive(false);

        $buffer = new BufferedOutput($output->getVerbosity(), $output->isDecorated(), $output->getFormatter());
        $statusCode = $application->run($subInput, $buffer);

        $details = trim($buffer->fetch());
        if ($details !== '' && $output->isVerbose()) {
            $output->writeln($details);
        }

        if ($statusCode !== Command::SUCCESS && $details !== '') {
            $output->writeln($details);
        }

        return $statusCode;
    }

    private function resolveDatabaseName(): ?string
    {
        $databaseUrl = (string) ($_SERVER['DATABASE_URL'] ?? $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL'));

        if ($databaseUrl === '') {
            return null;
        }

        if (str_starts_with($databaseUrl, 'sqlite:')) {
            return $databaseUrl;
        }

        $parsed = parse_url($databaseUrl);
        if (!is_array($parsed) || !isset($parsed['path'])) {
            return null;
        }

        $dbName = ltrim((string) $parsed['path'], '/');

        return $dbName !== '' ? $dbName : null;
    }
}
