<?php declare(strict_types=1);

namespace Qlimix\Queue\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Consumer\Exception\ConsumerException;
use Qlimix\Queue\Queue\QueueMessage;
use Throwable;

final class AmqpQueueConsumer implements ConsumerInterface
{
    private AmqpMessageFetcherInterface $fetcher;

    public function __construct(AmqpMessageFetcherInterface $fetcher)
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
        } catch (Throwable $exception) {
            throw new ConsumerException('Failed to consume', 0, $exception);
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
     * @return QueueMessage[]
     */
    private function convertToQueueMessages(array $amqpMessages): array
    {
        $queueMessages = [];
        foreach ($amqpMessages as $amqpMessage) {
            $queueMessages[] = new QueueMessage(
                $amqpMessage->delivery_info['delivery_tag'],
                $amqpMessage->body
            );
        }

        return $queueMessages;
    }
}
