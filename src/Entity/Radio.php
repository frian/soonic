<?php

namespace App\Entity;

use App\Repository\RadioRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(
    name: 'radio',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'UNIQ_RADIO_NAME', columns: ['name']),
        new ORM\UniqueConstraint(name: 'UNIQ_RADIO_STREAM_URL', columns: ['stream_url']),
    ]
)]
#[ORM\Entity(repositoryClass: RadioRepository::class)]
class Radio
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
    #[Assert\Url]
    #[Assert\Length(max: 512)]
    #[ORM\Column(length: 512)]
    private ?string $streamUrl = null;

    #[Assert\Url]
    #[Assert\Length(max: 512)]
    #[ORM\Column(length: 512, nullable: true)]
    private ?string $homepageUrl = null;

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

    public function getStreamUrl(): ?string
    {
        return $this->streamUrl;
    }

    public function setStreamUrl(string $streamUrl): self
    {
        $this->streamUrl = $this->normalizeUrl($streamUrl);

        return $this;
    }

    public function getHomepageUrl(): ?string
    {
        return $this->homepageUrl;
    }

    public function setHomepageUrl(?string $homepageUrl): self
    {
        if ($homepageUrl === null) {
            $this->homepageUrl = null;
            return $this;
        }

        $homepageUrl = trim($homepageUrl);
        $this->homepageUrl = $homepageUrl === '' ? null : $this->normalizeUrl($homepageUrl);

        return $this;
    }

    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }

        $scheme = isset($parts['scheme']) ? strtolower((string) $parts['scheme']) : null;
        $host = isset($parts['host']) ? strtolower((string) $parts['host']) : null;

        $normalized = '';
        if ($scheme !== null) {
            $normalized .= $scheme.'://';
        }

        if (isset($parts['user'])) {
            $normalized .= $parts['user'];
            if (isset($parts['pass'])) {
                $normalized .= ':'.$parts['pass'];
            }
            $normalized .= '@';
        }

        if ($host !== null) {
            $normalized .= $host;
        }

        if (isset($parts['port'])) {
            $normalized .= ':'.$parts['port'];
        }

        $path = $parts['path'] ?? '';
        if ($path !== '') {
            $path = rtrim($path, '/');
        }
        $normalized .= $path;

        if (isset($parts['query'])) {
            $normalized .= '?'.$parts['query'];
        }

        if (isset($parts['fragment'])) {
            $normalized .= '#'.$parts['fragment'];
        }

        return $normalized !== '' ? $normalized : $url;
    }
}
