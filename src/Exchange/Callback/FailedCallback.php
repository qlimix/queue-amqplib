<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange\Callback;

use PhpAmqpLib\Message\AMQPMessage;
use Qlimix\Queue\Exchange\AmqpFailedMessagesHolderInterface;

final class FailedCallback
{
    private AmqpFailedMessagesHolderInterface $failedMessageHolder;

    public function __construct(AmqpFailedMessagesHolderInterface $failedMessageHolder)
    {
        $this->failedMessageHolder = $failedMessageHolder;
    }

    public function callback(AMQPMessage $message): void
    {
        $this->failedMessageHolder->fail((string) $message->get('message_id'));
    }
}
