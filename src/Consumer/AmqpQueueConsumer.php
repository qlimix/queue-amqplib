<?php declare(strict_types=1);

namespace Qlimix\Queue\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Consumer\Exception\QueueConsumerException;
use Qlimix\Queue\Queue\QueueMessage;

final class AmqpQueueConsumer implements QueueConsumerInterface
{
    /** @var AmqpMessageFetcher */
    private $fetcher;

    /**
     * @param AmqpMessageFetcher $fetcher
     */
    public function __construct(AmqpMessageFetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    /**
     * @inheritDoc
     */
    public function consume(): array
    {
        try {
            return $this->convertToQueueMessages($this->fetcher->fetch());
        } catch (\Throwable $exception) {
            throw new QueueConsumerException('Failed to consume', 0, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function acknowledge(QueueMessage $message): void
    {
        $this->fetcher->acknowledge($message);
    }

    /**
     * @param AMQPMessage[] $amqpMessages
     *
     * @return array
     */
    private function convertToQueueMessages(array $amqpMessages): array
    {
        $queueMessages = [];
        foreach ($amqpMessages as $amqpMessage) {
            $queueMessages[] = new QueueMessage(
                $amqpMessage->delivery_info['delivery_tag'],
                (string)$amqpMessage->body
            );
        }

        return $queueMessages;
    }
}
