<?php declare(strict_types=1);

namespace Qlimix\Queue\Consumer;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Qlimix\Queue\Queue\QueueMessage;

final class AmqpMessageFetcher
{
    /** @var AMQPChannel */
    private $channel;

    /** @var AmqpMessageHolder */
    private $holder;

    /**
     * @param AMQPChannel $channel
     * @param AmqpMessageHolder $holder
     */
    public function __construct(AMQPChannel $channel, AmqpMessageHolder $holder)
    {
        $this->channel = $channel;
        $this->holder = $holder;
    }

    private function init(): void
    {
        $self = $this;
        $this->channel->basic_qos(0, $this->amount, false);
        $this->channel->basic_consume(
            $this->queue,
            '',
            false,
            false,
            false,
            false,
            function ($message) use ($self) {
                $self->messages[] = $message;
            }
        );
    }

    public function fetch(): array
    {
        try {
            $this->channel->wait(null, false, 0.1);
            return $this->holder->empty();
        } catch (AMQPTimeoutException $exception) {
            return $this->holder->empty();
        }
    }

    public function acknowledge(QueueMessage $message): void
    {
        $this->channel->basic_ack($message->getId());
    }
}
