<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class UserNotFoundException extends AbstractApiException implements ApiExceptionInterface
{
    public function __construct()
    {
        parent::__construct($this->getStatusCode(), sprintf("Couldn't find that user."));
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    public function getPayload(): array
    {
        return [
            'error_code' => 'invalid_credentials',
        ];
    }
}