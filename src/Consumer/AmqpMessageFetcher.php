<?php declare(strict_types=1);

namespace Qlimix\Queue\Consumer;

use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Channel\AmqpChannelConfiguratorInterface;
use Qlimix\Queue\Consumer\Exception\QueueConsumerException;
use Qlimix\Queue\Queue\QueueMessage;

final class AmqpMessageFetcher
{
    /** @var AmqpChannelConfiguratorInterface */
    private $amqpChannelConfigurator;

    /** @var AmqpMessageHolder */
    private $holder;

    /**
     * @param AmqpChannelConfiguratorInterface $amqpChannelConfigurator
     * @param AmqpMessageHolder $holder
     */
    public function __construct(AmqpChannelConfiguratorInterface $amqpChannelConfigurator, AmqpMessageHolder $holder)
    {
        $this->amqpChannelConfigurator = $amqpChannelConfigurator;
        $this->holder = $holder;
    }

    /**
     * @return AMQPMessage[]
     *
     * @throws QueueConsumerException
     */
    public function fetch(): array
    {
        try {
            $this->amqpChannelConfigurator->getChannel()->wait(null, false, 0.1);
            return $this->holder->empty();
        } catch (AMQPTimeoutException $exception) {
            return $this->holder->empty();
        } catch (\Throwable $exception) {
            throw new QueueConsumerException('Failed to fetch messages', 0, $exception);
        }
    }

    /**
     * @param QueueMessage $message
     *
     * @throws QueueConsumerException
     */
    public function acknowledge(QueueMessage $message): void
    {
        try {
            $this->amqpChannelConfigurator->getChannel()->basic_ack($message->getId());
        } catch (\Throwable $exception) {
            throw new QueueConsumerException('Failed to acknowledge message', 0, $exception);
        }
    }
}
