<?php declare(strict_types=1);

namespace Qlimix\Queue\Consumer;

use PhpAmqpLib\Message\AMQPMessage;

interface AmqpMessageHolderInterface
{
    public function addMessage(AMQPMessage $message): void;

    /**
     * @return AMQPMessage[]
     */
    public function empty(): array;
}
