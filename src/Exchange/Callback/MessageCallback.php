<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange\Callback;

use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Consumer\AmqpMessageHolder;

final class MessageCallback
{
    private AmqpMessageHolder $messageHolder;

    public function __construct(AmqpMessageHolder $messageHolder)
    {
        $this->messageHolder = $messageHolder;
    }

    public function callback(AMQPMessage $message): void
    {
        $this->messageHolder->addMessage($message);
    }
}
