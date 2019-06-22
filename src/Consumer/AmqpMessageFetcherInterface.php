<?php declare(strict_types=1);

namespace Qlimix\Queue\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Consumer\Exception\ConsumerException;
use Qlimix\Queue\Queue\QueueMessage;

interface AmqpMessageFetcherInterface
{
    /**
     * @return AMQPMessage[]
     *
     * @throws ConsumerException
     */
    public function fetch(): array;

    /**
     * @throws ConsumerException
     */
    public function acknowledge(QueueMessage $message): void;
}
