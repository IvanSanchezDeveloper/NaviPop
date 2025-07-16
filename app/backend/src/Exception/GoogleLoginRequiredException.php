<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class GoogleLoginRequiredException extends AbstractApiException implements ApiExceptionInterface
{
    public function __construct()
    {
        parent::__construct($this->getStatusCode(), sprintf('This user must log in via Google.'));
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    public function getPayload(): array
    {
        return [
            'error_code' => 'google_login_required',
        ];
    }

}