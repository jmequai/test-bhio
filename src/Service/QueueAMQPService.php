<?php

declare(strict_types=1);

namespace App\Service;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Publish to AMQP queue
 */
class QueueAMQPService implements QueueServiceInterface
{
    /**
     * @var string
     */
    private string $host;

    /**
     * @var int
     */
    private int $port;

    /**
     * @var string
     */
    private string $user;

    /**
     * @var string
     */
    private string $pass;

    /**
     * @var AMQPStreamConnection|null
     */
    private ?AMQPStreamConnection $connection = null;

    /**
     * @var AMQPChannel|null
     */
    private ?AMQPChannel $channel = null;

    /**
     * @param string $host
     * @param int $port
     * @param string $user
     * @param string $pass
     */
    public function __construct(string $host, int $port, string $user, string $pass)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return void
     */
    public function connect(): void
    {
        if (!$this->connection) {
            $this->connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->pass);
            $this->channel = $this->connection->channel();
        }
    }

    /**
     * @return void
     */
    public function close(): void
    {
        if ($this->connection) {
            try {
                $this->channel->close();
                $this->connection->close();
            } catch (\Throwable $e) {
                // empty
            } finally {
                $this->connection = null;
                $this->channel = null;
            }
        }
    }

    /**
     * @param string $exchange
     * @param string $queue
     * @param array $message
     * @return bool
     */
    public function publish(string $exchange, string $queue, array $message): bool
    {
        $this->connect();
        $this->bind($exchange, $queue);

        $message = new AMQPMessage(
            \serialize($message),
            [
                'content_type' => 'text/plain',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]
        );

        $this->channel->basic_publish($message, $exchange);

        return true;
    }

    /**
     * @param string $exchange
     * @param string $queue
     * @param string $consumer
     * @param \Closure $callback
     * @return void
     */
    public function consume(string $exchange, string $queue, string $consumer, \Closure $callback): void
    {
        $this->connect();
        $this->bind($exchange, $queue);

        $this->channel->basic_consume($queue, $consumer, false, false, false, false, $callback);

        $this->channel->consume();
    }

    /**
     * @param string $exchange
     * @param string $queue
     * @return void
     */
    private function bind(string $exchange, string $queue): void
    {
        $this->channel->queue_declare($queue, false, true, false, false);
        $this->channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

        $this->channel->queue_bind($queue, $exchange);
    }
}
