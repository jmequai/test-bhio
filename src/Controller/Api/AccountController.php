<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Request\Api\AccountEventCreateRequest;
use App\Service\QueueServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/account")
 */
final class AccountController extends AbstractController
{
    /**
     * @Route("/event", methods={"POST"}, name="account_event")
     * @param AccountEventCreateRequest $request
     * @param QueueServiceInterface $publisher
     * @return JsonResponse
     */
    public function event(
        AccountEventCreateRequest $request,
        QueueServiceInterface $publisher
    ): JsonResponse {
        try {
            $queue = $this->getParameter('amqp.queue.name');
            $exchange = $this->getParameter('amqp.exchange.name');

            $publisher->publish($exchange, $queue, $request->toArray());

            $response = ['result' => 'ok'];
        } catch (\Throwable $e) {
            // log error + retry message

            $response = ['error' => 'internal error'];
        }

        return $this->json($response);
    }
}
