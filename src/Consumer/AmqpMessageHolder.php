<?php declare(strict_types=1);

namespace Qlimix\Queue\Consumer;

use PhpAmqpLib\Message\AMQPMessage;

final class AmqpMessageHolder implements AmqpMessageHolderInterface
{
    /** @var AMQPMessage[] */
    private array $messages = [];

    public function addMessage(AMQPMessage $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * @return AMQPMessage[]
     */
    public function empty(): array
    {
        $messages = $this->messages;
        $this->messages = [];

        return $messages;
    }
}
