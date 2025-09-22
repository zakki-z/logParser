<?php

namespace App\Controller;

use App\Repository\LogEntryRepository;
use App\Service\SavingService;
use App\Service\ParsingService;
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
        $user = $this->getUser();
        $logs = $logRepository->findAllOrdered($user->getId());
        $uploadedFiles = $logRepository->getUploadedFiles($user->getId());


        return $this->render('log/index.html.twig', [
            'logs' => $logs,
            'uploadedFiles' => $uploadedFiles
        ]);
    }
    #[Route('/upload', name: 'log_upload', methods: ['POST'])]
    public function upload(Request $request, ParsingService $logParser, SavingService $saveLog): Response
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('logfile');

        if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_OK) {
            try {

                $user = $this->getUser();

                $result = $logParser->parseLogFile(
                    $uploadedFile->getPathname(),
                    $uploadedFile->getClientOriginalName(),
                    $user
                );
                    $saveLog->saveLogEntries($result['entries']);
                    $this->addFlash('success', $result['message']);

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
        $file = $this->getUser();
        $logRepository->clearAllByUser($file->getId());
        $this->addFlash('success', 'All your logs cleared');

        return $this->redirectToRoute('log_index');
    }
}
