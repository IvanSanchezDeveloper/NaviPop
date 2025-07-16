<?php

namespace App\Exception;

use App\Handler\ImageHandler;
use Symfony\Component\HttpFoundation\Response;

class ImageMaxSizeExceededException extends AbstractApiException implements ApiExceptionInterface
{
    public function __construct()
    {
        parent::__construct(
            $this->getStatusCode(),
            "Maximum file size (" . ImageHandler::MAX_IMG_SIZE_STRING . ") exceeded."
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_REQUEST_ENTITY_TOO_LARGE;
    }

    public function getPayload(): array
    {
        return [
            'error_code' => 'image_max_size_exceeded',
        ];
    }
}