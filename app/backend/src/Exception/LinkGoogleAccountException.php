<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class LinkGoogleAccountException extends AbstractApiException implements ApiExceptionInterface
{
    private string $email;

    public function __construct(string $email)
    {
        parent::__construct($this->getStatusCode(), 'Account exists. Please link your Google account.');
        $this->email    = $email;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }

    public function getPayload(): array
    {
        return [
            'error_code' => 'link_google_account',
            'email'      => $this->email,
        ];
    }
}