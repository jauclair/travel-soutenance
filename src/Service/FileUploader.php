<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file, $maxSize=0)
    {
        // Test du type mime
        if ($file->getClientMimeType() != 'image/jpeg' && $file->getClientMimeType() != 'image/png')
            return null;

        // Test de la taille mxi si présisée, sinon la taille maxi sera celle de php.ini
        if ($maxSize > 0 && $file->getClientSize() > $maxSize)
            return null;

        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            return null;
        }

        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
