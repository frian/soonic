<?php

namespace App\Tests\Command;

use App\Command\AddRadioCommand;
use App\Entity\Radio;
use App\Repository\RadioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddRadioCommandTest extends \PHPUnit\Framework\TestCase
{
    private EntityManagerInterface $entityManager;
    private RadioRepository $radioRepository;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->entityManager = $this->createStub(EntityManagerInterface::class);
        $this->radioRepository = $this->createStub(RadioRepository::class);
        $this->validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
    }

    public function testInvalidWhenRequiredArgumentsAreMissing(): void
    {
        $command = new AddRadioCommand($this->entityManager, $this->radioRepository, $this->validator);
        $tester = new CommandTester($command);

        $status = $tester->execute([], ['interactive' => false]);

        $this->assertSame(Command::INVALID, $status);
        $this->assertStringContainsString('Missing required arguments', $tester->getDisplay());
    }

    public function testFailsWhenNameAlreadyExists(): void
    {
        $radioRepository = $this->createMock(RadioRepository::class);
        $radioRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'Radio One'])
            ->willReturn(new Radio());

        $command = new AddRadioCommand($this->entityManager, $radioRepository, $this->validator);
        $tester = new CommandTester($command);

        $status = $tester->execute([
            'name' => 'Radio One',
            'stream-url' => 'https://example.com/stream',
        ], ['interactive' => false]);

        $this->assertSame(Command::FAILURE, $status);
        $this->assertStringContainsString('already exists', $tester->getDisplay());
    }

    public function testAddsRadioSuccessfully(): void
    {
        $radioRepository = $this->createMock(RadioRepository::class);
        $radioRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Radio::class));

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $command = new AddRadioCommand($entityManager, $radioRepository, $this->validator);
        $tester = new CommandTester($command);

        $status = $tester->execute([
            'name' => 'Radio Two',
            'stream-url' => 'https://example.com/live',
            'homepage-url' => 'https://example.com',
        ], ['interactive' => false]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('Radio added', $tester->getDisplay());
    }
}
