<?php

namespace App\Entity;

use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
class Album
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
    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255)]
    private ?string $albumSlug = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column]
    private ?int $songCount = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 8)]
    #[ORM\Column(length: 8)]
    private ?string $duration = null;

    #[Assert\Range(min: 1900, max: 2100)]
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $year = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $genre = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 1024)]
    #[ORM\Column(length: 1024)]
    private ?string $path = null;

    #[Assert\Length(max: 1024)]
    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $coverArtPath = null;

    #[ORM\ManyToMany(targetEntity: Artist::class, mappedBy: 'albums')]
    private Collection $artists;

    #[ORM\OneToMany(mappedBy: 'album', targetEntity: Song::class)]
    #[ORM\OrderBy(["trackNumber" => "ASC"])]
    private Collection $songs;

    public function __construct()
    {
        $this->artists = new ArrayCollection();
        $this->songs = new ArrayCollection();
    }

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

    public function getAlbumSlug(): ?string
    {
        return $this->albumSlug;
    }

    public function setAlbumSlug(string $albumSlug): self
    {
        $this->albumSlug = trim($albumSlug);

        return $this;
    }

    public function getSongCount(): ?int
    {
        return $this->songCount;
    }

    public function setSongCount(int $songCount): self
    {
        $this->songCount = $songCount;

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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = trim($path);

        return $this;
    }

    public function getCoverArtPath(): ?string
    {
        return $this->coverArtPath;
    }

    public function setCoverArtPath(?string $coverArtPath): self
    {
        $this->coverArtPath = $coverArtPath === null ? null : trim($coverArtPath);

        return $this;
    }

    /**
     * @return Collection<int, Artist>
     */
    public function getArtists(): Collection
    {
        return $this->artists;
    }

    public function addArtist(Artist $artist): self
    {
        if (!$this->artists->contains($artist)) {
            $this->artists->add($artist);
            $artist->addAlbum($this);
        }

        return $this;
    }

    public function removeArtist(Artist $artist): self
    {
        if ($this->artists->removeElement($artist)) {
            $artist->removeAlbum($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Song>
     */
    public function getSongs(): Collection
    {
        return $this->songs;
    }

    public function addSong(Song $song): self
    {
        if (!$this->songs->contains($song)) {
            $this->songs->add($song);
            $song->setAlbum($this);
        }

        return $this;
    }

    public function removeSong(Song $song): self
    {
        if ($this->songs->removeElement($song)) {
            // Song.album is non-nullable; do not nullify owning side here.
        }

        return $this;
    }
}
