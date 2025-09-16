<?php

namespace App\Controller;

use App\Repository\LogEntryRepository;
use App\Service\LogParserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/log')]
#[IsGranted('ROLE_USER')]
class LogController extends AbstractController
{
    #[Route('/', name: 'log_index')]
    public function index(LogEntryRepository $logRepository): Response
    {
        $logs = $logRepository->findAllOrdered();
        $uploadedFiles = $logRepository->getUploadedFiles();

        return $this->render('log/index.html.twig', [
            'logs' => $logs,
            'uploadedFiles' => $uploadedFiles,
        ]);
    }

    #[Route('/upload', name: 'log_upload', methods: ['POST'])]
    public function upload(Request $request, LogParserService $logParser): Response
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('logfile');

        if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_OK) {
            try {
                // Get the current authenticated user
                $user = $this->getUser();

                $result = $logParser->parseLogFile(
                    $uploadedFile->getPathname(),
                    $uploadedFile->getClientOriginalName(),
                    $user  // Pass the user to the service
                );

                if ($result['status'] === 'duplicate') {
                    $this->addFlash('warning', $result['message']);
                } else {
                    $logParser->saveLogEntries($result['entries']);
                    $this->addFlash('success', $result['message']);
                }

            } catch (\Exception $e) {
                $this->addFlash('error', 'Error parsing file: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Please select a valid file');
        }

        return $this->redirectToRoute('log_index');
    }

    #[Route('/clear', name: 'log_clear', methods: ['POST'])]
    public function clear(LogEntryRepository $logRepository): Response
    {
        // Clear only logs for the current user
        $user = $this->getUser();
        $logRepository->clearAllByUser($user->getId());
        $this->addFlash('success', 'All your logs cleared');

        return $this->redirectToRoute('log_index');
    }
}
