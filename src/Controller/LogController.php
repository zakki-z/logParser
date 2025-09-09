<?php

namespace App\Controller;

use App\Repository\LogEntryRepository;
use App\Service\LogParserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/log')]
class LogController extends AbstractController
{
    #[Route('/', name: 'log_index')]
    public function index(LogEntryRepository $logRepository): Response
    {
        $logs = $logRepository->findAllOrdered();

        return $this->render('log/index.html.twig', [
            'logs' => $logs,
        ]);
    }

    #[Route('/upload', name: 'log_upload', methods: ['POST'])]
    public function upload(Request $request, LogParserService $logParser): Response
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('logfile');

        if (!$uploadedFile) {
            $this->addFlash('error', 'No file was selected');
            return $this->redirectToRoute('log_index');
        }

        // Check for upload errors with specific messages
        $error = $uploadedFile->getError();
        if ($error !== UPLOAD_ERR_OK) {
            $errorMessage = match($error) {
                UPLOAD_ERR_INI_SIZE => 'File is too large (exceeds upload_max_filesize)',
                UPLOAD_ERR_FORM_SIZE => 'File is too large (exceeds form MAX_FILE_SIZE)',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
                default => 'Unknown upload error occurred'
            };

            $this->addFlash('error', $errorMessage);
            return $this->redirectToRoute('log_index');
        }

        // Additional validation
        if (!$uploadedFile->isValid()) {
            $this->addFlash('error', 'Invalid file upload');
            return $this->redirectToRoute('log_index');
        }

        // Check file size (additional client-side check)
        $maxSize = 50 * 1024 * 1024; // 50MB
        if ($uploadedFile->getSize() > $maxSize) {
            $this->addFlash('error', 'File is too large. Maximum size is 50MB');
            return $this->redirectToRoute('log_index');
        }

        // Check file type
        $allowedMimeTypes = ['text/plain', 'application/octet-stream'];
        if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
            $this->addFlash('error', 'Invalid file type. Please upload a text/log file');
            return $this->redirectToRoute('log_index');
        }

        $tempPath = $uploadedFile->getPathname();

        try {
            // Add file size info for user feedback
            $fileSizeMB = round($uploadedFile->getSize() / 1024 / 1024, 2);
            $this->addFlash('info', "Processing {$fileSizeMB}MB file. This may take a moment...");

            $parsedEntries = $logParser->parseLogFile($tempPath);
            $logParser->saveLogEntries($parsedEntries);

            $this->addFlash('success', count($parsedEntries) . ' log entries parsed successfully!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error parsing file: ' . $e->getMessage());
        }

        return $this->redirectToRoute('log_index');
    }

    #[Route('/clear', name: 'log_clear', methods: ['POST'])]
    public function clear(LogEntryRepository $logRepository): Response
    {
        $logRepository->clearAll();
        $this->addFlash('success', 'All logs cleared');

        return $this->redirectToRoute('log_index');
    }
}
