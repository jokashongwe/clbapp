<?php

namespace App\Entity;

use App\Repository\ParentEleveRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ParentEleveRepository::class)]
#[ORM\Table(name: 'parent_eleve')]
class ParentEleve
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $nomTuteur;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $numeroTelephoneTuteur;

    #[ORM\Column]
    #[Groups(['read'])]
    private bool $supp;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $telephonemere;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $nompere;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $telephonepere;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $nommere;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $datecreation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomTuteur(): string
    {
        return $this->nomTuteur;
    }

    public function getNumeroTelephoneTuteur(): string
    {
        return $this->numeroTelephoneTuteur;
    }

    public function isSupp(): bool
    {
        return $this->supp;
    }

    public function getTelephonemere(): string
    {
        return $this->telephonemere;
    }

    public function getNompere(): string
    {
        return $this->nompere;
    }

    public function getTelephonepere(): string
    {
        return $this->telephonepere;
    }

    public function getNommere(): string
    {
        return $this->nommere;
    }
}
