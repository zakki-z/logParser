<?php

namespace App\Service;

use App\Entity\File;
use App\Entity\LogEntry;
use App\Entity\User;
use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;

class ParsingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ){}
    public function parseLogFile(string $filePath, string $originalFilename, User $user): array
    {
        $fileSize = filesize($filePath);
        $file = new File();
        $file->setFileName($originalFilename);
        $file->setFileNameTime(time().'.'.$originalFilename);
        $file->setUploadedAt(new \DateTimeImmutable());
        $file->setFileSize($fileSize);
        $file->setUser($user);

        // Persist the file first to get an ID
        $this->entityManager->persist($file);
        $this->entityManager->flush();

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);

        $parsedEntries = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
                $pattern= '/^\[([^\]]+)\]\s+([^.]+)\.([A-Z]+):\s+(.+)$/';

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
                $logEntry->setFile($file);

                $parsedEntries[] = $logEntry;
            }
        }

        return [
            'status' => 'success',
            'message' => count($parsedEntries) . ' entries parsed successfully',
            'entries' => $parsedEntries,
            'file' => $file
        ];
    }
}
