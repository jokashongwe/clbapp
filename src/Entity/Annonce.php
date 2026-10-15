<?php

namespace App\Entity;

use App\Repository\AnnonceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AnnonceRepository::class)]
#[ORM\Table(name: 'annonce')]
class Annonce
{
    public const TYPE_INFORMATION = 'Information';
    public const TYPE_RAPPEL = 'Rappel';
    public const TYPE_CONVOCATION = 'Convocation';

    public const TYPES = [
        self::TYPE_INFORMATION,
        self::TYPE_RAPPEL,
        self::TYPE_CONVOCATION,
    ];

    public const CIBLE_TOUS = 'tous';
    public const CIBLE_ELEVE = 'eleve';
    public const CIBLE_CLASSE = 'classe';
    public const CIBLE_OPTION = 'option';

    public const CIBLE_TYPES = [
        self::CIBLE_TOUS,
        self::CIBLE_ELEVE,
        self::CIBLE_CLASSE,
        self::CIBLE_OPTION,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    #[Groups(['read'])]
    private string $type;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $titre;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['read'])]
    private string $contenu;

    #[ORM\Column(length: 32)]
    #[Groups(['read'])]
    private string $cibleType;

    #[ORM\Column(nullable: true)]
    #[Groups(['read'])]
    private ?int $cibleId = null;

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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    public function getContenu(): string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getCibleType(): string
    {
        return $this->cibleType;
    }

    public function setCibleType(string $cibleType): static
    {
        $this->cibleType = $cibleType;

        return $this;
    }

    public function getCibleId(): ?int
    {
        return $this->cibleId;
    }

    public function setCibleId(?int $cibleId): static
    {
        $this->cibleId = $cibleId;

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
