<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange\Callback;

use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Consumer\AmqpMessageHolderInterface;

final class MessageCallback
{
    /** @var AmqpMessageHolderInterface */
    private $messageHolder;

    public function __construct(AmqpMessageHolderInterface $messageHolder)
    {
        $this->messageHolder = $messageHolder;
    }

    public function callback(AMQPMessage $message): void
    {
        $this->messageHolder->addMessage($message);
    }
}
