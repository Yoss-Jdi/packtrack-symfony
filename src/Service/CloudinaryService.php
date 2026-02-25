<?php
namespace App\Service;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $config = Configuration::instance($_ENV['CLOUDINARY_URL']);
        $config->cloud->cloudName = 'dqzaed1qj';
        
        // Fix SSL sur Windows en local
        $config->api->uploadPrefix = 'https://api.cloudinary.com';
        
        $this->cloudinary = new Cloudinary($config);
    }

    public function uploadPdf(string $filePath, string $publicId): string
    {
        $result = $this->cloudinary->uploadApi()->upload($filePath, [
            'resource_type' => 'raw',
            'folder'        => 'factures',
            'public_id'     => $publicId,
            'verify'        => false, // ← désactive vérification SSL en local
        ]);

        return $result['secure_url'];
    }
}