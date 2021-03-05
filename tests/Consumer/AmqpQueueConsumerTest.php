<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Consumer;

use Exception;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Consumer\AmqpMessageFetcherInterface;
use Qlimix\Queue\Consumer\AmqpQueueConsumer;
use Qlimix\Queue\Consumer\Exception\ConsumerException;
use Qlimix\Queue\Queue\QueueMessage;

final class AmqpQueueConsumerTest extends TestCase
{
    public function testShouldConsume(): void
    {
        $fetcher = $this->createMock(AmqpMessageFetcherInterface::class);

        $mockedMessages = [];
        for ($i = 0; $i < 3; $i++) {
            $message = new AMQPMessage('test'.$i);
            $message->delivery_info['delivery_tag'] = 'test'.$i;
            $mockedMessages[] = $message;
        }

        $fetcher->expects(self::once())
            ->method('fetch')
            ->willReturn($mockedMessages);

        $consumer = new AmqpQueueConsumer($fetcher);

        $messages = $consumer->consume();

        for ($i = 0; $i < 3; $i++) {
            self::assertSame($messages[$i]->getMessage(), 'test'.$i);
        }
    }

    public function testShouldThrowOnConsumeException(): void
    {
        $fetcher = $this->createMock(AmqpMessageFetcherInterface::class);

        $fetcher->expects(self::once())
            ->method('fetch')
            ->willThrowException(new Exception());

        $consumer = new AmqpQueueConsumer($fetcher);

        $this->expectException(ConsumerException::class);

        $consumer->consume();
    }

    public function testShouldAcknowledge(): void
    {
        $fetcher = $this->createMock(AmqpMessageFetcherInterface::class);

        $fetcher->expects(self::once())
            ->method('acknowledge');

        $consumer = new AmqpQueueConsumer($fetcher);

        $consumer->acknowledge(new QueueMessage('1', 'message'));
    }

    public function testShouldThrowOnAcknowledgeException(): void
    {
        $fetcher = $this->createMock(AmqpMessageFetcherInterface::class);

        $fetcher->expects(self::once())
            ->method('acknowledge')
            ->willThrowException(new ConsumerException());

        $consumer = new AmqpQueueConsumer($fetcher);

        $this->expectException(ConsumerException::class);

        $consumer->acknowledge(new QueueMessage('1', 'message'));
    }
}
