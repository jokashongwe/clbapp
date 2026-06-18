<?php

namespace App\Entity;

use App\Repository\AnneeScolaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AnneeScolaireRepository::class)]
#[ORM\Table(name: 'annee_scolaire')]
class AnneeScolaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read'])]
    private ?string $status = null;

    #[ORM\Column(name: 'annee_scolaire', length: 255)]
    #[Groups(['read'])]
    private string $anneeScolaire;

    #[ORM\Column(name: 'date_debut', type: Types::DATE_MUTABLE)]
    #[Groups(['read'])]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(name: 'date_fin', type: Types::DATE_MUTABLE)]
    #[Groups(['read'])]
    private \DateTimeInterface $dateFin;

    #[ORM\Column]
    private bool $supp;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getAnneeScolaire(): string
    {
        return $this->anneeScolaire;
    }

    public function getDateDebut(): \DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function getDateFin(): \DateTimeInterface
    {
        return $this->dateFin;
    }

    public function isSupp(): bool
    {
        return $this->supp;
    }
}
