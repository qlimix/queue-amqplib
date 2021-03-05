<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange\Callback;

use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Exchange\AmqpFailedMessagesHolder;
use Qlimix\Queue\Exchange\Callback\FailedCallback;

final class FailedCallbackTest extends TestCase
{
    public function testShouldCallback(): void
    {
        $value = 'foo';

        $failedMessageHolder = new AmqpFailedMessagesHolder();

        $callback = new FailedCallback($failedMessageHolder);
        $callback->callback(new AMQPMessage('', ['message_id' => $value]));

        self::assertTrue($failedMessageHolder->hasFailed());
    }
}
