<?php

namespace App\Entity;

use App\Repository\AbonnementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AbonnementRepository::class)]
#[ORM\Table(name: 'abonnement')]
class Abonnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $cle;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['read'])]
    private \DateTimeInterface $datedebut;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['read'])]
    private \DateTimeInterface $datefin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCle(): string
    {
        return $this->cle;
    }

    public function getDatedebut(): \DateTimeInterface
    {
        return $this->datedebut;
    }

    public function getDatefin(): \DateTimeInterface
    {
        return $this->datefin;
    }

    public function isActif(\DateTimeInterface $at = new \DateTimeImmutable()): bool
    {
        return $this->datedebut <= $at && $this->datefin >= $at;
    }
}
