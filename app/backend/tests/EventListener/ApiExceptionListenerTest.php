<?php

namespace App\Tests\EventListener;

use App\EventListener\ApiExceptionListener;
use App\Exception\AbstractApiException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ApiExceptionListenerTest extends TestCase
{
    private const ERROR_STATUS_CODE = 400;

    private ApiExceptionListener $listener;
    private HttpKernelInterface $kernel;
    private Request $request;

    protected function setUp(): void
    {
        $this->listener = new ApiExceptionListener();
        $this->kernel = $this->createMock(HttpKernelInterface::class);
        $this->request = new Request();
    }

    private function createExceptionEvent(\Throwable $exception): ExceptionEvent
    {
        return new ExceptionEvent($this->kernel, $this->request, HttpKernelInterface::MAIN_REQUEST, $exception);
    }

    private function createApiExceptionMock(): AbstractApiException
    {
        $exception = $this->createMock(AbstractApiException::class);

        $exception->method('getStatusCode')->willReturn(self::ERROR_STATUS_CODE);

        return $exception;
    }

    public function testOnKernelExceptionWithApiExceptionSetsJsonResponse(): void
    {
        $exception = $this->createApiExceptionMock();

        $event = $this->createExceptionEvent($exception);

        $this->listener->onKernelException($event);

        $response = $event->getResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(self::ERROR_STATUS_CODE, $response->getStatusCode());
    }

    public function testOnKernelExceptionWithOtherExceptionDoesNothing(): void
    {
        $exception = new \Exception('Generic error');
        $event = $this->createExceptionEvent($exception);

        $this->listener->onKernelException($event);

        $this->assertNull($event->getResponse());
    }
}
