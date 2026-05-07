<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Arrete;
use App\Entity\Equivalence;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Twig\Environment;

final class PdfGenerator
{
    private readonly string $tempDir;
    private readonly string $projectDir;

    public function __construct(
        private readonly Environment $twig,
        private readonly ArreteGenerator $arreteGenerator,
        string $kernelProjectDir
    ) {
        $this->projectDir = $kernelProjectDir;
        $this->tempDir = $kernelProjectDir . '/var/tmp/pdf';
    }

    public function generateArrete(Equivalence $equivalence): BinaryFileResponse
    {
        $this->ensureTempDirectoryExists();

        $logoPath = $this->projectDir . '/public/uploads/images/logo.png';

        $html = $this->twig->render('pdf/arrete.html.twig', [
            'equivalence' => $equivalence,
            'logoPath' => $logoPath,
        ]);

        $filename = $this->buildFilename($equivalence);
        $filepath = sprintf('%s/%s', $this->tempDir, $filename);

        $this->arreteGenerator->generate($html, $filepath);

        $response = new BinaryFileResponse($filepath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
        $response->deleteFileAfterSend(true);

        return $response;
    }

    public function generateArreteFromArrete(Arrete $arrete): BinaryFileResponse
    {
        $this->ensureTempDirectoryExists();

        $logoPath = $this->projectDir . '/public/uploads/images/logo.png';

        $html = $this->twig->render('pdf/arrete_document.html.twig', [
            'arrete' => $arrete,
            'logoPath' => $logoPath,
        ]);

        $filename = sprintf(
            'arrete_%s.pdf',
            preg_replace('/[^A-Za-z0-9\-_]/', '_', $arrete->getNumeroArrete() ?? 'document')
        );
        $filepath = sprintf('%s/%s', $this->tempDir, $filename);

        $this->arreteGenerator->generate($html, $filepath);

        $response = new BinaryFileResponse($filepath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
        $response->deleteFileAfterSend(true);

        return $response;
    }

    private function ensureTempDirectoryExists(): void
    {
        if (is_dir($this->tempDir)) {
            return;
        }

        if (!mkdir($concurrentDirectory = $this->tempDir, 0775, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Impossible de créer le répertoire temporaire "%s".', $this->tempDir));
        }
    }

    private function buildFilename(Equivalence $equivalence): string
    {
        $numeroDossier = preg_replace('/[^A-Za-z0-9\-_]/', '_', $equivalence->getNumeroDossier() ?? 'document');
        return sprintf('arrete_%s.pdf', $numeroDossier);
    }
}
