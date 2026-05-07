<?php

namespace App\Controller;

use App\Entity\Document;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/document')]
class DocumentController extends AbstractController
{
    #[Route('/download/{id}', name: 'document_download', methods: ['GET'])]
    #[IsGranted('VIEW', subject: 'document')] // optionnel : utiliser un voter
    public function download(Document $document, Request $request): BinaryFileResponse
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public' . $document->getPath();

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Le fichier n\'existe pas.');
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $document->getOriginalName()
        );

        return $response;
    }
}
