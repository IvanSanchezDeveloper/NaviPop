<?php

namespace App\EventListener;

use App\Exception\AbstractApiException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ApiExceptionListener implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof AbstractApiException) {
            $response = new JsonResponse([
                'message' => $exception->getMessage(),
                'payload' => $exception->getPayload(),
            ], $exception->getStatusCode());

            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => 'onKernelException',
        ];
    }
}