<?php

namespace App\Exception;

class GoogleLoginRequiredException extends AbstractApiException implements ApiExceptionInterface
{
    public function __construct(string $email)
    {
        parent::__construct($this->getStatusCode(), sprintf('User %s must log in via Google.', $email));
    }

    public function getStatusCode(): int
    {
        return self::STATUS_UNAUTHORIZED;
    }

    public function getPayload(): array
    {
        return [
            'error_code' => 'google_login_required',
        ];
    }

}