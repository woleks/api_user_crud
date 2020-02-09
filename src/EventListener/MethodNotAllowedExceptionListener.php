<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class MethodNotAllowedExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->getThrowable() instanceof MethodNotAllowedHttpException) {
            $event->setResponse(new JsonResponse([
                'error' => [
                    'message' => 'Method ' . $event->getRequest()->getMethod() . ' Not Allowed Here',
                    'code' => JsonResponse::HTTP_METHOD_NOT_ALLOWED
                ]], JsonResponse::HTTP_METHOD_NOT_ALLOWED));
        }
    }
}
