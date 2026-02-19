<?php

namespace App\Entity;

use App\Repository\LanguageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(
    name: 'language',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'UNIQ_LANGUAGE_CODE', columns: ['code']),
    ]
)]
#[ORM\Entity(repositoryClass: LanguageRepository::class)]
class Language
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 8)]
    #[Assert\Regex(pattern: '/^[a-z]{2,3}(?:[_-][A-Za-z]{2,3})?$/')]
    #[ORM\Column(length: 8)]
    private ?string $code = null;

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = trim($name);

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $code = trim($code);
        $this->code = str_contains($code, '_')
            ? strtolower(substr($code, 0, 2)).'_'.strtoupper(substr($code, 3))
            : strtolower($code);

        return $this;
    }
}
