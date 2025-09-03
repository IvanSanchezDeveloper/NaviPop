<?php

namespace App\Handler;

use App\Exception\ImageMaxSizeExceededException;
use App\Exception\ImageTypeNotAllowedException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageHandler
{
    public const MAX_IMG_SIZE_STRING = "5MB";

    private const MAX_IMG_SIZE = 5 * 1024 * 1024; // 5 MB

    public const ALLOWED_IMG_TYPES = ['jpeg', 'jpg', 'png'];

    private const ALLOWED_MIME_IMG_TYPES = ['image/jpeg', 'image/jpg', 'image/png'];

    public const IMAGE_WEBP_EXTENSION = 'webp';

    private static ?\Imagick $imagick = null;


    public static function validateImage(UploadedFile $image): void
    {
        if (!in_array($image->getMimeType(), self::ALLOWED_MIME_IMG_TYPES)) {
            throw new ImageTypeNotAllowedException();
        }

        if ($image->getSize() > self::MAX_IMG_SIZE) {
            throw new ImageMaxSizeExceededException();
        }

    }

    public static function getUniqueFileName(UploadedFile $image): string
    {
        $fileName = uniqid() . '.' . self::IMAGE_WEBP_EXTENSION;

        return $fileName;
    }

    public static function saveImage(UploadedFile $image, string $uploadDir, string $fileName, int $quality = 80): void
    {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $imagick = self::$imagick ?? new \Imagick($image->getPathname());
        $imagick->setImageFormat('webp');
        $imagick->setImageCompressionQuality($quality);

        $webpFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.webp';
        $webpPath = rtrim($uploadDir, '/') . '/' . $webpFileName;

        $imagick->writeImage($webpPath);

        $imagick->clear();
    }

    // For testing purposes only
    public static function setImagick(?\Imagick $imagick): void
    {
        self::$imagick = $imagick;
    }
}