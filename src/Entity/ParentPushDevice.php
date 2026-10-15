<?php

namespace App\Entity;

use App\Repository\ParentPushDeviceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParentPushDeviceRepository::class)]
#[ORM\Table(name: 'parent_push_device')]
class ParentPushDevice
{
    public const PLATFORM_ANDROID = 'android';
    public const PLATFORM_IOS = 'ios';

    public const PLATFORMS = [
        self::PLATFORM_ANDROID,
        self::PLATFORM_IOS,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ParentEleve::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ParentEleve $parent;

    #[ORM\Column(length: 512, unique: true)]
    private string $deviceToken;

    #[ORM\Column(length: 16)]
    private string $platform;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDeviceToken(): string
    {
        return $this->deviceToken;
    }

    public function setDeviceToken(string $deviceToken): static
    {
        $this->deviceToken = $deviceToken;

        return $this;
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function setPlatform(string $platform): static
    {
        $this->platform = $platform;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
