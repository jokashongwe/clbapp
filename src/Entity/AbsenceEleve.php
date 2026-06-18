<?php

namespace App\Entity;

use App\Repository\AbsenceEleveRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AbsenceEleveRepository::class)]
#[ORM\Table(name: 'absence_eleve')]
class AbsenceEleve
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read'])]
    private ?string $section = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read'])]
    private ?string $classe = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read'])]
    private ?string $eleve = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read'])]
    private ?string $statut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['read'])]
    private \DateTimeInterface $dateAbsence;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read'])]
    private ?string $anneescolaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function getClasse(): ?string
    {
        return $this->classe;
    }

    public function getEleve(): ?string
    {
        return $this->eleve;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function getDateAbsence(): \DateTimeInterface
    {
        return $this->dateAbsence;
    }

    public function getAnneescolaire(): ?string
    {
        return $this->anneescolaire;
    }
}
