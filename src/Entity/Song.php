<?php

namespace App\Entity;

use App\Repository\SongRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(
    name: 'song',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'UNIQ_SONG_WEB_PATH', columns: ['web_path']),
    ]
)]
#[ORM\Entity(repositoryClass: SongRepository::class)]
class Song
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 1024)]
    #[ORM\Column(length: 1024)]
    private ?string $path = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 1024)]
    #[ORM\Column(length: 1024)]
    private ?string $webPath = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 512)]
    #[ORM\Column(length: 512)]
    private ?string $title = null;

    #[Assert\Positive]
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $trackNumber = null;

    #[Assert\Range(min: 1900, max: 2100)]
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $year = null;

    #[Assert\Length(max: 64)]
    #[ORM\Column(length: 64, nullable: true)]
    private ?string $genre = null;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d{1,2}:\d{2}(:\d{2})?$/')]
    #[ORM\Column(length: 8)]
    private ?string $duration = null;

    #[ORM\ManyToOne(inversedBy: 'songs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Album $album = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Artist $artist = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = trim($path);

        return $this;
    }

    public function getWebPath(): ?string
    {
        return $this->webPath;
    }

    public function setWebPath(string $webPath): self
    {
        $this->webPath = trim($webPath);

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = trim($title);

        return $this;
    }

    public function getTrackNumber(): ?int
    {
        return $this->trackNumber;
    }

    public function setTrackNumber(int $trackNumber): self
    {
        $this->trackNumber = $trackNumber;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): self
    {
        $this->genre = $genre === null ? null : trim($genre);

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): self
    {
        $this->duration = trim($duration);

        return $this;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(Album $album): self
    {
        $this->album = $album;

        return $this;
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(Artist $artist): self
    {
        $this->artist = $artist;

        return $this;
    }
}
