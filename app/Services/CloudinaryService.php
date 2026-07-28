<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);
    }

    /**
     * Upload an image to Cloudinary
     */
    public function upload(UploadedFile $file, string $folder = 'products'): string
    {
        try {
            $result = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                [
                    'folder' => $folder,
                    'use_filename' => true,
                    'unique_filename' => true,
                    'overwrite' => false,
                ]
            );

            return $result['secure_url'];
        } catch (\Exception $e) {
            throw new \Exception('Cloudinary upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete an image from Cloudinary
     */
    public function delete(string $publicId): bool
    {
        try {
            $result = $this->cloudinary->uploadApi()->destroy($publicId);
            return $result['result'] === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the public ID from a Cloudinary URL
     */
    public function getPublicId(string $url): string
    {
        $parts = explode('/', $url);
        $filename = end($parts);
        return pathinfo($filename, PATHINFO_FILENAME);
    }
}