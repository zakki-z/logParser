<?php

namespace App\Service;

use App\Entity\LogEntry;
use Doctrine\ORM\EntityManagerInterface;

class LogParserService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function parseLogFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File not found');
        }
        if (!is_readable($filePath)) {
            throw new \Exception('File not readable');
        }
        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);

        $parsedEntries = [];

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

                $parsedEntries[] = $logEntry;
            }
        }

        return $parsedEntries;
    }

    public function saveLogEntries(array $logEntries): void
    {
        foreach ($logEntries as $logEntry) {
            $this->entityManager->persist($logEntry);
        }
        $this->entityManager->flush();
    }
}
