<?php

namespace App\Entity;

use App\Repository\PaiementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
#[ORM\Table(name: 'paiement')]
class Paiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Tarification::class)]
    #[ORM\JoinColumn(name: 'tarification_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['read'])]
    private Tarification $tarification;

    #[ORM\ManyToOne(targetEntity: Eleve::class)]
    #[ORM\JoinColumn(name: 'eleve_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['read'])]
    private Eleve $eleve;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read'])]
    private ?string $detail = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['read'])]
    private ?string $montantUsd = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['read'])]
    private ?string $montantFc = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['read'])]
    private ?string $montant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['read'])]
    private ?\DateTimeInterface $datepaiement = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read'])]
    private ?string $numRecu = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read'])]
    private ?string $mois = null;

    #[ORM\Column(name: 'anneescolaire_id', nullable: true)]
    #[Groups(['read'])]
    private ?int $anneescolaireId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read'])]
    private ?string $typePaiement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTarification(): Tarification
    {
        return $this->tarification;
    }

    public function getEleve(): Eleve
    {
        return $this->eleve;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function getMontantUsd(): ?string
    {
        return $this->montantUsd;
    }

    public function getMontantFc(): ?string
    {
        return $this->montantFc;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function getDatepaiement(): ?\DateTimeInterface
    {
        return $this->datepaiement;
    }

    public function getNumRecu(): ?string
    {
        return $this->numRecu;
    }

    public function getMois(): ?string
    {
        return $this->mois;
    }

    public function getAnneescolaireId(): ?int
    {
        return $this->anneescolaireId;
    }

    public function getTypePaiement(): ?string
    {
        return $this->typePaiement;
    }
}
