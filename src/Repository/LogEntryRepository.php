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
     * Find all log entries ordered by date (newest first)
     */
    public function findAllOrdered(): array
    {
        return $this->findBy([], ['date' => 'DESC']);
    }

    /**
     * Get unique uploaded files with their log entry counts
     */
    public function getUploadedFiles(): array
    {
        // Get all files from the File repository instead
        $fileRepository = $this->getEntityManager()->getRepository(\App\Entity\File::class);
        $files = $fileRepository->findBy([], ['uploaded_at' => 'DESC']); // Use the actual property name

        $result = [];
        foreach ($files as $file) {
            $logEntries = $this->findBy(['file' => $file], ['date' => 'DESC']);
            $result[] = [
                'id' => $file->getId(),
                'fileName' => $file->getFileName(),
                'uploadedAt' => $file->getUploadedAt(),
                'fileSize' => $file->getFileSize(),
                'logCount' => count($logEntries),
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

    /**
     * Clear all log entries (admin function)
     */
    public function clearAll(): void
    {
        $this->createQueryBuilder('l')
            ->delete()
            ->getQuery()
            ->execute();
    }
}
