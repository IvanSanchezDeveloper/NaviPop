<?php

namespace App\Exception;

class WrongCredentialsException extends AbstractApiException implements ApiExceptionInterface
{
    public function __construct()
    {
        parent::__construct($this->getStatusCode(), 'Wrong credentials.');
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