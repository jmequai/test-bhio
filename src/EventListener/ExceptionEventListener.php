<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 *
 */
class ExceptionEventListener
{
    /**
     * @param ExceptionEvent $event
     * @return void
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        $response = new JsonResponse(
            [
                'error' => $e->getMessage(),
            ],
            500
        );

        $event->setResponse($response);
    }
}
