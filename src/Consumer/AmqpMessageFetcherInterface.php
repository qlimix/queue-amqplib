<?php declare(strict_types=1);

namespace Qlimix\Queue\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Consumer\Exception\QueueConsumerException;
use Qlimix\Queue\Queue\QueueMessage;

interface AmqpMessageFetcherInterface
{

    /**
     * @return AMQPMessage[]
     *
     * @throws QueueConsumerException
     */
    public function fetch(): array;

    /**
     * @throws QueueConsumerException
     */
    public function acknowledge(QueueMessage $message): void;
}
