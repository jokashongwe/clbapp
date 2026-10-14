<?php

namespace App\Entity;

use App\Repository\DevoirRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DevoirRepository::class)]
#[ORM\Table(name: 'devoir')]
class Devoir
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(name: 'classe_id')]
    #[Groups(['read'])]
    private int $classeId;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $titre;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['read'])]
    private ?string $description = null;

    #[ORM\Column(length: 512)]
    private string $storageKey;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $originalFilename;

    #[ORM\Column(length: 128)]
    #[Groups(['read'])]
    private string $mimeType;

    #[ORM\Column]
    #[Groups(['read'])]
    private int $fileSize;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: ParentEleve::class)]
    #[ORM\JoinColumn(name: 'created_by_parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?ParentEleve $createdBy = null;

    #[ORM\Column]
    private bool $supp = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClasseId(): int
    {
        return $this->classeId;
    }

    public function setClasseId(int $classeId): static
    {
        $this->classeId = $classeId;

        return $this;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStorageKey(): string
    {
        return $this->storageKey;
    }

    public function setStorageKey(string $storageKey): static
    {
        $this->storageKey = $storageKey;

        return $this;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?ParentEleve
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?ParentEleve $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function isSupp(): bool
    {
        return $this->supp;
    }

    public function setSupp(bool $supp): static
    {
        $this->supp = $supp;

        return $this;
    }
}
