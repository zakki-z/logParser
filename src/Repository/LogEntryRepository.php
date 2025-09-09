<?php

namespace App\Repository;

use App\Entity\LogEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LogEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogEntry::class);
    }

    public function save(LogEntry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function clearAll(): void
    {
        $this->createQueryBuilder('l')
            ->delete()
            ->getQuery()
            ->execute();
    }
}
