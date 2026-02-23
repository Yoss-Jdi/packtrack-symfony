<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger
    ) {}

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
        $file->move($this->getTargetDirectory(), $fileName);
        return $fileName;
    }

    public function uploadBase64(string $base64Data): string
    {
        // Extraire les données base64 (format: data:image/jpeg;base64,XXXX)
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
            $data = substr($base64Data, strpos($base64Data, ',') + 1);
            $data = base64_decode($data);
            $fileName = 'photo-' . uniqid() . '.' . $extension;
            $filePath = $this->getTargetDirectory() . '/' . $fileName;
            file_put_contents($filePath, $data);
            return $fileName;
        }
        throw new \InvalidArgumentException('Format base64 invalide');
    }

    public function delete(string $fileName): void
    {
        $filePath = $this->getTargetDirectory() . '/' . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
