<?php

namespace App\Exception;

class LinkGoogleAccountException extends AbstractApiException implements ApiExceptionInterface
{
    private string $googleId;
    private string $email;

    public function __construct(string $email, string $googleId)
    {
        parent::__construct($this->getStatusCode(), 'Account exists—please link your Google login.');
        $this->googleId = $googleId;
        $this->email    = $email;
    }

    public function getStatusCode(): int
    {
        return self::STATUS_CONFLICT;
    }

    public function getPayload(): array
    {
        return [
            'error_code' => 'link_google_account',
            'email'      => $this->email,
            'google_id'  => $this->googleId,
        ];
    }
}