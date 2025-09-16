<?php

namespace App\Repository;

use App\Entity\LogEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<LogEntry>
 */
class LogEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogEntry::class);
    }
    /**
     * Find all log entries ordered by date (newest first) for current user
     */
    public function findAllOrdered(?Uuid $userId = null): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.file', 'f')
            ->orderBy('l.date', 'DESC');

        if ($userId) {
            $qb->where('f.user = :userId')
                ->setParameter('userId', $userId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get unique uploaded files with their log entry counts for current user
     */
    public function getUploadedFiles(?Uuid $userId = null): array
    {
        $fileRepository = $this->getEntityManager()->getRepository(\App\Entity\File::class);

        $criteria = [];
        if ($userId) {
            $criteria['user'] = $userId;
        }

        $files = $fileRepository->findBy($criteria, ['uploaded_at' => 'DESC']);

        $result = [];
        foreach ($files as $file) {
            $logEntries = $this->findBy(['file' => $file], ['date' => 'DESC']);
            $result[] = [
                'id' => $file->getId(),
                'filename' => $file->getFileName(),
                'uploaded_at' => $file->getUploadedAt(),
                'file_size' => $file->getFileSize(),
                'entry_count' => count($logEntries),
                'logEntries' => $logEntries
            ];
        }

        return $result;
    }

    /**
     * Clear all log entries for a specific user
     */
    /**
     * Clear all log entries for a specific user
     */
    public function clearAllByUser(Uuid $userId): void
    {
        $this->createQueryBuilder('l')
            ->delete()
            ->where('l.file IN (
            SELECT f.id FROM App\Entity\File f WHERE f.user = :userId
        )')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }
}
