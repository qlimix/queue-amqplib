<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange\Callback;

use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Exchange\AmqpFailedMessagesHolderInterface;
use Qlimix\Queue\Exchange\Callback\FailedCallback;

final class FailedCallbackTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCallback(): void
    {
        $value = 'foo';

        $failedMessageHolder = $this->createMock(AmqpFailedMessagesHolderInterface::class);

        $failedMessageHolder->expects($this->once())
            ->method('fail')
            ->with($this->callback(static function(string $message) use (&$value) {
                return $message === $value;
            }));

        $callback = new FailedCallback($failedMessageHolder);
        $callback->callback(new AMQPMessage('', ['message_id' => $value]));
    }
}
