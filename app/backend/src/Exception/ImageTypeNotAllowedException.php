<?php

namespace App\Exception;

use App\Handler\ImageHandler;
use Symfony\Component\HttpFoundation\Response;

class ImageTypeNotAllowedException extends AbstractApiException implements ApiExceptionInterface
{
    public function __construct()
    {
        parent::__construct(
            $this->getStatusCode(),
            sprintf(
                "Image type not allowed. Only %s are allowed.",
                implode(', ', ImageHandler::ALLOWED_IMG_TYPES)
            )
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_UNSUPPORTED_MEDIA_TYPE;
    }

    public function getPayload(): array
    {
        return [
            'error_code' => 'image_type_not_allowed',
        ];
    }
}