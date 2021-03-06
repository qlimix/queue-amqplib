<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange\Callback;

use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Consumer\AmqpMessageHolder;
use Qlimix\Queue\Exchange\Callback\MessageCallback;

final class MessageCallbackTest extends TestCase
{
    public function testShouldCallback(): void
    {
        $key = 'message_id';
        $value = 'foo';

        $messageHolder = new AmqpMessageHolder();

        $callback = new MessageCallback($messageHolder);
        $callback->callback(new AMQPMessage('', ['message_id' => $value]));

        $messages = $messageHolder->empty();
        self::assertCount(1, $messages);
        self::assertSame($value, $messages[0]->get($key));
    }
}
