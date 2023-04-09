<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Publish to queue
 */
interface QueueServiceInterface
{
    /**
     * @return void
     */
    public function connect(): void;

    /**
     * @return void
     */
    public function close(): void;

    /**
     * @param string $exchange
     * @param string $queue
     * @param array $message
     * @return bool
     */
    public function publish(string $exchange, string $queue, array $message): bool;

    /**
     * @param string $exchange
     * @param string $queue
     * @param string $consumer
     * @param \Closure $callback
     * @return void
     */
    public function consume(string $exchange, string $queue, string $consumer, \Closure $callback): void;
}
