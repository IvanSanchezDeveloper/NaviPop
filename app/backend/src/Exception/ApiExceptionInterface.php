<?php

namespace App\Exception;

interface ApiExceptionInterface
{
    public function getStatusCode(): int;
    public function getPayload(): array;
}