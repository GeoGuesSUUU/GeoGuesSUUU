<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (
            str_starts_with($_SERVER['REQUEST_URI'], '/api')
        ) {
                // Only Exception from '^/api' are thrown in JSON format

                $data = [
                    'status' => $exception instanceof HttpException ? $exception->getStatusCode() : 400,
                    'message' => $exception->getMessage()
                ];
                $event->setResponse(new JsonResponse($data));
        }


        // Keep standard exception in HTML format
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}

