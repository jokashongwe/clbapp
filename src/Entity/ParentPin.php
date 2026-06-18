<?php

namespace App\Entity;

use App\Repository\ParentPinRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParentPinRepository::class)]
#[ORM\Table(name: 'parent_pin')]
class ParentPin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ParentEleve::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ParentEleve $parent;

    #[ORM\Column(length: 32, unique: true)]
    private string $telephone;

    #[ORM\Column(length: 255)]
    private string $pinHash;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ParentEleve
    {
        return $this->parent;
    }

    public function setParent(ParentEleve $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPinHash(): string
    {
        return $this->pinHash;
    }

    public function setPinHash(string $pinHash): static
    {
        $this->pinHash = $pinHash;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
