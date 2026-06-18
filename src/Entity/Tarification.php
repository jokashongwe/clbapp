<?php

namespace App\Entity;

use App\Repository\TarificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TarificationRepository::class)]
#[ORM\Table(name: 'tarification')]
class Tarification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $nom;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['read'])]
    private ?array $section = null;

    #[ORM\Column]
    #[Groups(['read'])]
    private bool $isPaiementDirect;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read'])]
    private ?string $devise = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getSection(): ?array
    {
        return $this->section;
    }

    public function isPaiementDirect(): bool
    {
        return $this->isPaiementDirect;
    }

    public function getDevise(): ?string
    {
        return $this->devise;
    }
}
