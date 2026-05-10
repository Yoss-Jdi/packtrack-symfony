<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private const STORAGE_PREFIX = 'images/profiles';

    public function __construct(
        private string $targetDirectory,
        private string $legacyDirectory,
        private SluggerInterface $slugger
    ) {}

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.png';

        $this->ensureTargetDirectoryExists();

        $binaryData = file_get_contents($file->getPathname());
        if ($binaryData === false) {
            throw new \RuntimeException('Impossible de lire le fichier uploadé.');
        }

        $this->writeImageAsPng($binaryData, $this->getTargetDirectory() . DIRECTORY_SEPARATOR . $fileName);

        return self::STORAGE_PREFIX . '/' . $fileName;
    }

    public function uploadBase64(string $base64Data): string
    {
        // Extraire les données base64 (format: data:image/jpeg;base64,XXXX)
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            $data = substr($base64Data, strpos($base64Data, ',') + 1);
            $data = base64_decode($data);
            if ($data === false) {
                throw new \InvalidArgumentException('Format base64 invalide');
            }

            $fileName = 'photo-' . uniqid() . '.png';

            $this->ensureTargetDirectoryExists();
            $this->writeImageAsPng($data, $this->getTargetDirectory() . DIRECTORY_SEPARATOR . $fileName);

            return self::STORAGE_PREFIX . '/' . $fileName;
        }
        throw new \InvalidArgumentException('Format base64 invalide');
    }

    public function delete(string $fileName): void
    {
        foreach ($this->resolveAbsolutePaths($fileName) as $filePath) {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    public function normalizeStoredPath(string $fileName): string
    {
        $normalized = str_replace('\\', '/', trim($fileName));

        if (str_starts_with($normalized, self::STORAGE_PREFIX . '/')) {
            return $normalized;
        }

        return self::STORAGE_PREFIX . '/' . basename($normalized);
    }

    private function ensureTargetDirectoryExists(): void
    {
        if (!is_dir($this->targetDirectory) && !mkdir($this->targetDirectory, 0775, true) && !is_dir($this->targetDirectory)) {
            throw new \RuntimeException(sprintf('Impossible de créer le dossier d\'images "%s".', $this->targetDirectory));
        }
    }

    private function resolveAbsolutePath(string $fileName): string
    {
        return $this->resolveAbsolutePaths($fileName)[0];
    }

    private function resolveAbsolutePaths(string $fileName): array
    {
        $normalized = $this->normalizeStoredPath($fileName);
        $baseName = basename($normalized);

        return [
            $this->getTargetDirectory() . DIRECTORY_SEPARATOR . $baseName,
            $this->legacyDirectory . DIRECTORY_SEPARATOR . $baseName,
        ];
    }

    private function writeImageAsPng(string $binaryData, string $targetPath): void
    {
        if (function_exists('imagecreatefromstring') && function_exists('imagepng')) {
            $image = @imagecreatefromstring($binaryData);

            if ($image !== false) {
                imagepng($image, $targetPath);
                imagedestroy($image);
                return;
            }
        }

        file_put_contents($targetPath, $binaryData);
    }
}
