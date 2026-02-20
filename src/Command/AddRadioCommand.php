<?php

namespace App\Command;

use App\Entity\Radio;
use App\Repository\RadioRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'soonic:add:radio',
    description: 'Add a radio station (name, stream URL, optional homepage URL).'
)]
class AddRadioCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RadioRepository $radioRepository,
        private readonly ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'Radio name')
            ->addArgument('stream-url', InputArgument::OPTIONAL, 'Radio stream URL')
            ->addArgument('homepage-url', InputArgument::OPTIONAL, 'Radio homepage URL (optional)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = trim((string) ($input->getArgument('name') ?? ''));
        $streamUrl = trim((string) ($input->getArgument('stream-url') ?? ''));
        $homepageUrl = trim((string) ($input->getArgument('homepage-url') ?? ''));

        if ($input->isInteractive()) {
            if ($name === '') {
                $name = trim($io->ask('Radio name') ?? '');
            }
            if ($streamUrl === '') {
                $streamUrl = trim($io->ask('Stream URL (https://...)') ?? '');
            }
            if ($homepageUrl === '') {
                $homepageUrl = trim((string) ($io->ask('Homepage URL (optional)', '') ?? ''));
            }
        }

        if ($name === '' || $streamUrl === '') {
            $io->error('Missing required arguments. Usage: soonic:add:radio "Name" "https://stream.url" ["https://homepage.url"]');

            return Command::INVALID;
        }

        $radio = new Radio();
        $radio->setName($name);
        $radio->setStreamUrl($streamUrl);
        $radio->setHomepageUrl($homepageUrl === '' ? null : $homepageUrl);

        $validationErrors = $this->validator->validate($radio);
        if (count($validationErrors) > 0) {
            $messages = [];
            foreach ($validationErrors as $error) {
                $messages[] = sprintf('%s: %s', $error->getPropertyPath(), $error->getMessage());
            }
            $io->error($messages);

            return Command::INVALID;
        }

        if ($this->radioRepository->findOneBy(['name' => $radio->getName()]) !== null) {
            $io->error(sprintf('A radio named "%s" already exists.', $radio->getName()));

            return Command::FAILURE;
        }

        if ($this->radioRepository->findOneBy(['streamUrl' => $radio->getStreamUrl()]) !== null) {
            $io->error(sprintf('A radio with stream URL "%s" already exists.', $radio->getStreamUrl()));

            return Command::FAILURE;
        }

        try {
            $this->entityManager->persist($radio);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            $io->error('Radio already exists (unique constraint violation).');

            return Command::FAILURE;
        }

        $io->success(sprintf('Radio added (id: %d): %s', $radio->getId(), $radio->getName()));

        return Command::SUCCESS;
    }
}
