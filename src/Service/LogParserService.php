<?php

namespace App\Service;

use App\Entity\LogEntry;
use Doctrine\ORM\EntityManagerInterface;

class LogParserService
{
    private const BATCH_SIZE = 1000; // Process in batches to avoid memory issues

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function parseLogFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('Log file does not exist');
        }

        $fileSize = filesize($filePath);
        if ($fileSize === false || $fileSize === 0) {
            throw new \InvalidArgumentException('Invalid or empty log file');
        }

        // For very large files, use file reading instead of loading entire file
        if ($fileSize > 10 * 1024 * 1024) { // 10MB threshold
            return $this->parseLogFileByLines($filePath);
        }

        // For smaller files, use the original method
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException('Could not read log file');
        }

        $lines = explode("\n", $content);
        return $this->parseLines($lines);
    }

    private function parseLogFileByLines(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('Could not open log file for reading');
        }

        $parsedEntries = [];
        $lineNumber = 0;

        try {
            while (($line = fgets($handle)) !== false) {
                $lineNumber++;
                $line = trim($line);

                if (empty($line)) {
                    continue;
                }

                try {
                    $logEntry = $this->parseLine($line);
                    if ($logEntry) {
                        $parsedEntries[] = $logEntry;
                    }
                } catch (\Exception $e) {
                    // Log parsing error but continue with other lines
                    error_log("Error parsing line {$lineNumber}: " . $e->getMessage());
                }
            }
        } finally {
            fclose($handle);
        }

        return $parsedEntries;
    }

    private function parseLines(array $lines): array
    {
        $parsedEntries = [];

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            try {
                $logEntry = $this->parseLine($line);
                if ($logEntry) {
                    $parsedEntries[] = $logEntry;
                }
            } catch (\Exception $e) {
                // Log parsing error but continue with other lines
                error_log("Error parsing line " . ($lineNumber + 1) . ": " . $e->getMessage());
            }
        }

        return $parsedEntries;
    }

    private function parseLine(string $line): ?LogEntry
    {
        // Original pattern: [timestamp] channel.TYPE: message
        $pattern = '/^\[([^\]]+)\]\s+([^.]+)\.([A-Z]+):\s+(.+)$/';

        if (!preg_match($pattern, $line, $matches)) {
            // Try alternative patterns or skip invalid lines
            return null;
        }

        $logEntry = new LogEntry();

        try {
            $date = new \DateTime($matches[1]);
            $logEntry->setDate($date);
        } catch (\Exception $e) {
            throw new \RuntimeException("Invalid date format: {$matches[1]}");
        }

        $logEntry->setChannel($matches[2]);
        $logEntry->setType($matches[3]);
        $logEntry->setInformation($matches[4]);

        return $logEntry;
    }

    public function saveLogEntries(array $logEntries): void
    {
        $count = 0;

        foreach ($logEntries as $logEntry) {
            $this->entityManager->persist($logEntry);
            $count++;

            // Flush in batches to avoid memory issues
            if ($count % self::BATCH_SIZE === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear(); // Clear memory
            }
        }

        // Flush remaining entries
        if ($count % self::BATCH_SIZE !== 0) {
            $this->entityManager->flush();
        }

        $this->entityManager->clear();
    }
}
