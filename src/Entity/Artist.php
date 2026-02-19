<?php

namespace App\Entity;

use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(
    name: 'artist',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'UNIQ_ARTIST_SLUG', columns: ['artist_slug']),
    ]
)]
#[ORM\Entity(repositoryClass: ArtistRepository::class)]
class Artist
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
    private ?string $artistSlug = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $albumCount = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $coverArtPath = null;

    #[ORM\ManyToMany(targetEntity: Album::class, inversedBy: 'artists')]
    private Collection $albums;

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function __construct()
    {
        $this->albums = new ArrayCollection();
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

    public function getArtistSlug(): ?string
    {
        return $this->artistSlug;
    }

    public function setArtistSlug(string $artistSlug): self
    {
        $this->artistSlug = trim($artistSlug);

        return $this;
    }

    public function getAlbumCount(): ?int
    {
        return $this->albumCount;
    }

    public function setAlbumCount(int $albumCount): self
    {
        $this->albumCount = $albumCount;

        return $this;
    }

    public function getCoverArtPath(): ?string
    {
        return $this->coverArtPath;
    }

    public function setCoverArtPath(?string $coverArtPath): self
    {
        $this->coverArtPath = $coverArtPath;

        return $this;
    }

    /**
     * @return Collection<int, Album>
     */
    public function getAlbums(): Collection
    {
        return $this->albums;
    }

    public function addAlbum(Album $album): self
    {
        if (!$this->albums->contains($album)) {
            $this->albums->add($album);
            $album->addArtist($this);
        }

        return $this;
    }

    public function removeAlbum(Album $album): self
    {
        if ($this->albums->removeElement($album)) {
            $album->removeArtist($this);
        }

        return $this;
    }
}
