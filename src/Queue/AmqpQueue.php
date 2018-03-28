<?php declare(strict_types=1);

namespace Qlimix\Queue\Queue;

use PhpAmqpLib\Channel\AMQPChannel;
use Qlimix\Queue\Amqp\AmqpConnectionFactory;
use Qlimix\Queue\Job\Job;
use Qlimix\Queue\Message\MessageInterface;
use Qlimix\Queue\Queue\Exception\InvalidQueueMessageException;
use Qlimix\Queue\Queue\Exception\QueueEmptyException;

final class AmqpQueue implements QueueInterface
{
    /** @var AmqpConnectionFactory */
    private $amqpConnectionFactory;

    /** @var string */
    private $queue;

    /** @var AMQPChannel */
    private $channel;

    /**
     * @param AmqpConnectionFactory $amqpConnectionFactory
     * @param string $queue
     */
    public function __construct(AmqpConnectionFactory $amqpConnectionFactory, string $queue)
    {
        $this->amqpConnectionFactory = $amqpConnectionFactory;
        $this->queue = $queue;
    }

    /**
     * @inheritDoc
     */
    public function getMessage(): QueueMessage
    {
        $channel = $this->getChannel();

        $amqpMessage = $channel->basic_get($this->queue);

        if ($amqpMessage === null) {
            throw new QueueEmptyException('Queue \''.$this->queue.'\' is empty');
        }

        try {
            $arrayJob = json_decode($amqpMessage->body, true);
        } catch (\Throwable $exception) {
            throw new InvalidQueueMessageException(
                'Could not decode from json',
                $exception,
                $amqpMessage->delivery_info['delivery_tag'],
                (string)$amqpMessage->body
            );
        }

        try {
            $job = Job::deserialize($arrayJob);
        } catch (\Throwable $exception) {
            throw new InvalidQueueMessageException(
                'Could not decode from json',
                $exception,
                $amqpMessage->delivery_info['delivery_tag'],
                (string)$amqpMessage->body
            );
        }

        if (!$job->getMessage() instanceof MessageInterface) {
            throw new InvalidQueueMessageException(
                'Invalid message does not implement MessageInterface',
                null,
                $amqpMessage->delivery_info['delivery_tag'],
                (string)$amqpMessage->body
            );
        }

        return new QueueMessage($amqpMessage->delivery_info['delivery_tag'], $job);
    }

    /**
     * @inheritDoc
     */
    public function acknowledge(QueueMessage $queueMessage): void
    {
        $this->getChannel()->basic_ack($queueMessage->getId());
    }

    /**
     * @return AMQPChannel
     */
    private function getChannel(): AMQPChannel
    {
        if ($this->channel === null) {
            $this->channel = $this->amqpConnectionFactory->getConnection()->channel();
            $this->channel->basic_qos(0, 1, false);
        }

        return $this->channel;
    }
}
