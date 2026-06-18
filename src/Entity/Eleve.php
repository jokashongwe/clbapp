<?php

namespace App\Entity;

use App\Repository\EleveRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: EleveRepository::class)]
#[ORM\Table(name: 'eleve')]
class Eleve
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ParentEleve::class)]
    #[ORM\JoinColumn(name: 'id_parent_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['read'])]
    private ?ParentEleve $parent = null;

    #[ORM\Column(name: 'id_classe_id', nullable: true)]
    #[Groups(['read'])]
    private ?int $classeId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $nom;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $postNom;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $prenom;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $sexe;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private string $matricule;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['read'])]
    private \DateTimeInterface $dateNaissance;

    #[ORM\Column]
    #[Groups(['read'])]
    private bool $supp;

    #[ORM\Column(name: 'famille_id', nullable: true)]
    #[Groups(['read'])]
    private ?int $familleId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?ParentEleve
    {
        return $this->parent;
    }

    public function getClasseId(): ?int
    {
        return $this->classeId;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getPostNom(): string
    {
        return $this->postNom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getSexe(): string
    {
        return $this->sexe;
    }

    public function getMatricule(): string
    {
        return $this->matricule;
    }

    public function getDateNaissance(): \DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function isSupp(): bool
    {
        return $this->supp;
    }

    public function getFamilleId(): ?int
    {
        return $this->familleId;
    }

    /**
     * Formats possibles du champ `eleve` dans absence_eleve.
     *
     * @return string[]
     */
    public function getPossibleAbsenceLabels(): array
    {
        $labels = [
            trim($this->prenom.' '.$this->nom.' '.$this->postNom),
            trim($this->nom.' '.$this->postNom.' '.$this->prenom),
            trim($this->nom.' '.$this->prenom),
            trim($this->prenom.' '.$this->nom),
            trim($this->matricule),
        ];

        $expanded = [];
        foreach ($labels as $label) {
            if ($label === '') {
                continue;
            }
            $expanded[] = $label;
            $expanded[] = mb_strtoupper($label);
        }

        return array_values(array_unique($expanded));
    }
}
