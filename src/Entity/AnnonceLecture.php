<?php

namespace App\Entity;

use App\Repository\AnnonceLectureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnnonceLectureRepository::class)]
#[ORM\Table(name: 'annonce_lecture')]
#[ORM\UniqueConstraint(name: 'UNIQ_ANNONCE_LECTURE', columns: ['annonce_id', 'parent_id'])]
class AnnonceLecture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Annonce::class)]
    #[ORM\JoinColumn(name: 'annonce_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Annonce $annonce;

    #[ORM\ManyToOne(targetEntity: ParentEleve::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ParentEleve $parent;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $readAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnnonce(): Annonce
    {
        return $this->annonce;
    }

    public function setAnnonce(Annonce $annonce): static
    {
        $this->annonce = $annonce;

        return $this;
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

    public function getReadAt(): \DateTimeImmutable
    {
        return $this->readAt;
    }

    public function setReadAt(\DateTimeImmutable $readAt): static
    {
        $this->readAt = $readAt;

        return $this;
    }
}
