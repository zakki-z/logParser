<?php

namespace App\Service;

use App\Entity\LogEntry;
use App\Repository\LogEntryRepository;
use Doctrine\ORM\EntityManagerInterface;

class LogParserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LogEntryRepository $logRepository
    ) {
    }

    public function parseLogFile(string $filePath, string $originalFilename): array
    {
        $fileHash = hash_file('sha256', $filePath);
        $fileSize = filesize($filePath);

        $existingFile = $this->logRepository->findByFileHash($fileHash);
        if (!empty($existingFile)) {
            $uploadDate = $existingFile[0]->getUploadedAt()->format('d/m/Y H:i:s');
            return [
                'status' => 'duplicate',
                'message' => "File '{$originalFilename}' was already uploaded on {$uploadDate}",
                'entries' => []
            ];
        }

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);

        $parsedEntries = [];
        $uploadedAt = new \DateTime();

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $pattern = '/^\[([^\]]+)\]\s+([^.]+)\.([A-Z]+):\s+(.+)$/';

            if (preg_match($pattern, $line, $matches)) {
                $logEntry = new LogEntry();

                try {
                    $date = new \DateTime($matches[1]);
                    $logEntry->setDate($date);
                } catch (\Exception $e) {
                    continue;
                }

                $logEntry->setChannel($matches[2]);
                $logEntry->setType($matches[3]);
                $logEntry->setInformation($matches[4]);
                $logEntry->setFilename($originalFilename);
                $logEntry->setFolderName('uploads');
                $logEntry->setFileHash($fileHash);
                $logEntry->setUploadedAt($uploadedAt);
                $logEntry->setFileSize($fileSize);

                $parsedEntries[] = $logEntry;
            }
        }

        return [
            'status' => 'success',
            'message' => count($parsedEntries) . ' entries parsed successfully',
            'entries' => $parsedEntries
        ];
    }

    public function saveLogEntries(array $logEntries): void
    {
        foreach ($logEntries as $logEntry) {
            $this->entityManager->persist($logEntry);
        }
        $this->entityManager->flush();
    }
}
