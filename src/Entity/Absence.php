<?php

namespace App\Entity;

use App\Repository\AbsenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AbsenceRepository::class)]
#[ORM\Table(name: 'absences')]
class Absence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(name: 'agentaffectation_id')]
    #[Groups(['read'])]
    private int $agentaffectationId;

    #[ORM\ManyToOne(targetEntity: TypeAbsence::class)]
    #[ORM\JoinColumn(name: 'typeabsences_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['read'])]
    private TypeAbsence $typeAbsence;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $observation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgentaffectationId(): int
    {
        return $this->agentaffectationId;
    }

    public function getTypeAbsence(): TypeAbsence
    {
        return $this->typeAbsence;
    }

    public function getObservation(): string
    {
        return $this->observation;
    }
}
