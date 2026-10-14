<?php

namespace App\Repository;

use App\Entity\ParentEleve;
use App\Entity\ParentPushDevice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParentPushDevice>
 */
class ParentPushDeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParentPushDevice::class);
    }

    public function register(ParentEleve $parent, string $deviceToken, string $platform): ParentPushDevice
    {
        $existing = $this->findOneBy(['deviceToken' => $deviceToken]);
        $now = new \DateTimeImmutable();

        if ($existing !== null) {
            $existing
                ->setParent($parent)
                ->setPlatform($platform)
                ->setUpdatedAt($now);
            $this->getEntityManager()->flush();

            return $existing;
        }

        $device = (new ParentPushDevice())
            ->setParent($parent)
            ->setDeviceToken($deviceToken)
            ->setPlatform($platform)
            ->setUpdatedAt($now);

        $em = $this->getEntityManager();
        $em->persist($device);
        $em->flush();

        return $device;
    }

    public function unregisterByToken(string $deviceToken): bool
    {
        $device = $this->findOneBy(['deviceToken' => $deviceToken]);
        if ($device === null) {
            return false;
        }

        $this->getEntityManager()->remove($device);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @param int[] $parentIds
     *
     * @return string[]
     */
    public function findDeviceTokensForParentIds(array $parentIds): array
    {
        if ($parentIds === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('d')
            ->select('d.deviceToken')
            ->innerJoin('d.parent', 'p')
            ->andWhere('p.id IN (:parentIds)')
            ->setParameter('parentIds', $parentIds)
            ->getQuery()
            ->getSingleColumnResult();

        return array_values(array_unique(array_map('strval', $rows)));
    }

    public function removeByToken(string $deviceToken): void
    {
        $device = $this->findOneBy(['deviceToken' => $deviceToken]);
        if ($device === null) {
            return;
        }

        $this->getEntityManager()->remove($device);
        $this->getEntityManager()->flush();
    }
}
