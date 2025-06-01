<?php

namespace App\Exception;

interface ApiExceptionInterface
{
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_CONFLICT = 409;

    public function getStatusCode(): int;
    public function getPayload(): array;
}