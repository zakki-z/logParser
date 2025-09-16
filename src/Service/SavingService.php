<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class SavingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }
    public function saveLogEntries(array $logEntries): void
    {
        foreach ($logEntries as $logEntry) {
            $this->entityManager->persist($logEntry);
        }
        $this->entityManager->flush();
    }
}
