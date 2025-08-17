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
        $fileName = uniqid() . '.' . $image->guessExtension();

        return $fileName;
    }

    public static function saveImage(UploadedFile $image, string $uploadDir, string $fileName): void
    {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $image->move($uploadDir, $fileName);
    }
}