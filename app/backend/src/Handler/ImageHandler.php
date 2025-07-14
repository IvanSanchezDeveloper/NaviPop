<?php

namespace App\Handler;

use App\Exception\ImageMaxSizeExceededException;
use App\Exception\ImageTypeNotAllowedException;

class ImageHandler
{
    public const MAX_IMG_SIZE_STRING = "5MB";

    private const MAX_IMG_SIZE = 5 * 1024 * 1024; // 5 MB

    public const ALLOWED_IMG_TYPES = ['jpeg', 'png'];

    private const ALLOWED_MIME_IMG_TYPES = ['image/jpeg', 'image/png'];

    public static function validateImage(mixed $image): void
    {
        if (!in_array($image->getMimeType(), self::ALLOWED_MIME_IMG_TYPES)) {
            throw new ImageTypeNotAllowedException();
        }

        if ($image->getSize() > self::MAX_IMG_SIZE) {
            throw new ImageMaxSizeExceededException();
        }

    }

    public static function getImageFileName(mixed $image): string
    {
        $fileName = uniqid() . '.' . $image->guessExtension();

        return $fileName;
    }

    public static function saveImage(mixed $image, string $uploadDir): void
    {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = self::getImageFileName($image);

        $image->move($uploadDir, $fileName);

    }
}