<?php
namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ConsumptionImport
{
    private $file;

    /** @return UploadedFile $file */
    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file): void
    {
        $this->file = $file;
    }
}