<?php

namespace App\Repository;

use App\Entity\LogEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
     * Find log entries by file name (through the associated File entity)
     */
    public function findByFileName(string $fileName): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.file', 'f')
            ->where('f.fileName = :fileName')
            ->setParameter('fileName', $fileName)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find log entries by file ID
     */
    public function findByFileId(int $fileId): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.file = :fileId')
            ->setParameter('fileId', $fileId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find log entries by user (through the associated File entity)
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.file', 'f')
            ->where('f.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('l.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find log entries by channel
     */
    public function findByChannel(string $channel): array
    {
        return $this->findBy(['channel' => $channel]);
    }

    /**
     * Find log entries by type
     */
    public function findByType(string $type): array
    {
        return $this->findBy(['type' => $type]);
    }

    /**
     * Find log entries within a date range
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.date BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('l.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all log entries ordered by date (newest first) for current user
     */
    public function findAllOrdered(?int $userId = null): array
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
    public function getUploadedFiles(?int $userId = null): array
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
    public function clearAllByUser(int $userId): void
    {
        $this->createQueryBuilder('l')
            ->delete()
            ->leftJoin('l.file', 'f')
            ->where('f.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }
}
