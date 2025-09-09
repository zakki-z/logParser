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

    public function findByFileHash(string $fileHash): ?array
    {
        return $this->createQueryBuilder('l')
            ->where('l.file_hash = :hash')
            ->setParameter('hash', $fileHash)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    public function getUploadedFiles(): array
    {
        return $this->createQueryBuilder('l')
            ->select('DISTINCT l.filename, l.file_hash, l.uploaded_at, l.file_size')
            ->addSelect('COUNT(l.id) as entry_count')
            ->groupBy('l.file_hash, l.filename, l.uploaded_at, l.file_size')
            ->orderBy('l.uploaded_at', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
