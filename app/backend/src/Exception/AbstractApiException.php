<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class AbstractApiException extends HttpException implements ApiExceptionInterface
{

    abstract public function getPayload(): array;

    public function getStatusCode(): int
    {
        return $this->getStatusCode();
    }
}