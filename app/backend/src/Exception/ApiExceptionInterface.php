<?php

namespace App\Exception;

interface ApiExceptionInterface
{
    const int STATUS_UNAUTHORIZED = 401;
    const int STATUS_CONFLICT = 409;

    public function getStatusCode(): int;
    public function getPayload(): array;
}