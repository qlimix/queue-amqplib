<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange\Callback;

use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Consumer\AmqpMessageHolderInterface;

final class MessageCallback
{
    private AmqpMessageHolderInterface $messageHolder;

    public function __construct(AmqpMessageHolderInterface $messageHolder)
    {
        $this->messageHolder = $messageHolder;
    }

    public function callback(AMQPMessage $message): void
    {
        $this->messageHolder->addMessage($message);
    }
}
