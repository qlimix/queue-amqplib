<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Consumer\AmqpMessageHolder;

final class AmqpMessageHolderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldHoldMessages(): void
    {
        $holder = new AmqpMessageHolder();

        for ($i = 0; $i < 3; $i++) {
            $holder->addMessage(new AMQPMessage('test'.$i));
        }

        $messages = $holder->empty();

        for ($i = 0; $i < 3; $i++) {
            $this->assertSame($messages[$i]->body, 'test'.$i);
        }
    }

    /**
     * @test
     */
    public function shouldEmptyOnEmpty(): void
    {
        $holder = new AmqpMessageHolder();

        for ($i = 0; $i < 3; $i++) {
            $holder->addMessage(new AMQPMessage('test'));
        }

        $holder->empty();
        $messages = $holder->empty();

        $this->assertSame([], $messages);
    }
}
