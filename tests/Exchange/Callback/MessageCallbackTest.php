<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange\Callback;

use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Consumer\AmqpMessageHolderInterface;
use Qlimix\Queue\Exchange\Callback\MessageCallback;

final class MessageCallbackTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCallback(): void
    {
        $key = 'message_id';
        $value = 'foo';

        $messageHolder = $this->createMock(AmqpMessageHolderInterface::class);

        $messageHolder->expects($this->once())
            ->method('addMessage')
            ->with($this->callback(static function(AMQPMessage $message) use (&$key, &$value) {
                return $message->get($key) === $value;
            }));

        $callback = new MessageCallback($messageHolder);
        $callback->callback(new AMQPMessage('', ['message_id' => $value]));
    }
}
