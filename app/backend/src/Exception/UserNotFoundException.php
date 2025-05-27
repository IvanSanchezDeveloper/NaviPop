<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UserNotFoundException extends AbstractApiException implements ApiExceptionInterface
{
    public function __construct()
    {
        parent::__construct($this->getStatusCode(), sprintf("Couldn't find that user."));
    }

    public function getStatusCode(): int
    {
        return self::STATUS_UNAUTHORIZED;
    }

    public function getPayload(): array
    {
        return [
            'error_code' => 'invalid_credentials',
        ];
    }
}