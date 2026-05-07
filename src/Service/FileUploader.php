<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function upload(
        UploadedFile $file,
        string $directory
    ): string {

        $filename =
            uniqid().
            '.'.
            $file->guessExtension();

        $file->move(
            $directory,
            $filename
        );

        return $filename;
    }
}