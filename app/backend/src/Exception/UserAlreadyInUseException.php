<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class UserAlreadyInUseException extends AbstractApiException implements ApiExceptionInterface
{
    public function __construct()
    {
        parent::__construct($this->getStatusCode(), sprintf("User already in use."));
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }

    public function getPayload(): array
    {
        return [
            'error_code' => 'user_already_in_use',
        ];
    }
}