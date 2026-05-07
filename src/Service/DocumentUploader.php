<?php

namespace App\Service;

use App\Entity\Document;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class DocumentUploader
{
    private string $uploadDirectory;

    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly LoggerInterface $logger
    ) {
        $this->uploadDirectory = dirname(__DIR__, 2) . '/public/uploads/documents';
    }

    // src/Service/DocumentUploader.php

public function upload(
    UploadedFile $file,
    Document $document,
    string $type
): void {

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (!$file->isValid()) {

        throw new \RuntimeException(
            sprintf(
                'Fichier invalide (code %d).',
                $file->getError()
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MIME TYPES AUTORISÉS
    |--------------------------------------------------------------------------
    */

    $allowedMimeTypes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    $mimeType = $file->getMimeType();

    if (!in_array($mimeType, $allowedMimeTypes, true)) {

        throw new \RuntimeException(
            'Type de fichier non autorisé.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TAILLE
    |--------------------------------------------------------------------------
    */

    $fileSize = $file->getSize();

    if ($fileSize === false || $fileSize === null) {

        throw new \RuntimeException(
            'Impossible de lire la taille du fichier.'
        );
    }

    $maxSize = 10 * 1024 * 1024;

    if ($fileSize > $maxSize) {

        throw new \RuntimeException(
            'Le fichier dépasse 10 Mo.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DOSSIER DESTINATION
    |--------------------------------------------------------------------------
    */

    if (!is_dir($this->uploadDirectory)) {

        if (
            !mkdir($this->uploadDirectory, 0775, true)
            && !is_dir($this->uploadDirectory)
        ) {
            throw new \RuntimeException(
                'Impossible de créer le dossier de destination.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NOM FICHIER
    |--------------------------------------------------------------------------
    */

    $originalName = pathinfo(
        $file->getClientOriginalName(),
        PATHINFO_FILENAME
    );

    $safeName = $this->slugger->slug($originalName);

    $extension = $file->guessExtension() ?: 'bin';

    $newFilename =
        strtoupper($type)
        . '-'
        . $safeName
        . '-'
        . uniqid('', true)
        . '.'
        . $extension;

    /*
    |--------------------------------------------------------------------------
    | MOVE SECURISE SYMFONY
    |--------------------------------------------------------------------------
    */

    try {

        $file->move(
            $this->uploadDirectory,
            $newFilename
        );

    } catch (FileException $e) {

        $this->logger->error('Erreur upload document', [
            'type' => $type,
            'message' => $e->getMessage(),
        ]);

        throw new \RuntimeException(
            'Erreur lors de l’enregistrement du fichier.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HYDRATATION DOCUMENT
    |--------------------------------------------------------------------------
    */

    $document->setFilename($newFilename);

    $document->setOriginalName(
        $file->getClientOriginalName()
    );

    $document->setPath(
        '/uploads/documents/' . $newFilename
    );

    $document->setMimeType($mimeType);

    $document->setSize((int) $fileSize);

    $document->setType($type);
}
}
