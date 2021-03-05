<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Consumer;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Channel\ChannelProviderInterface;
use Qlimix\Queue\Consumer\AmqpMessageFetcher;
use Qlimix\Queue\Consumer\AmqpMessageHolder;
use Qlimix\Queue\Consumer\Exception\ConsumerException;
use Qlimix\Queue\Queue\QueueMessage;

final class AmqpFetchMessageTest extends TestCase
{
    public function testShouldFetchMessage(): void
    {
        $holder = new AmqpMessageHolder();

        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::once())
            ->method('wait');

        $configurator = $this->createMock(ChannelProviderInterface::class);
        $configurator->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $fetcher = new AmqpMessageFetcher($configurator, $holder);

        $fetched = $fetcher->fetch();

        self::assertSame([], $fetched);
    }

    public function testShouldFetchMessageOnTimeoutException(): void
    {
        $holder = new AmqpMessageHolder();

        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::once())
            ->method('wait')
            ->willThrowException(new AMQPTimeoutException());

        $configurator = $this->createMock(ChannelProviderInterface::class);
        $configurator->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $fetcher = new AmqpMessageFetcher($configurator, $holder);

        $fetched = $fetcher->fetch();

        self::assertSame([], $fetched);
    }

    public function testShouldThrowOnConnectionException(): void
    {
        $holder = new AmqpMessageHolder();

        $configurator = $this->createMock(ChannelProviderInterface::class);
        $configurator->expects(self::once())
            ->method('getChannel')
            ->willThrowException(new Exception());

        $fetcher = new AmqpMessageFetcher($configurator, $holder);

        $this->expectException(ConsumerException::class);

        $fetcher->fetch();
    }

    public function testShouldAcknowledge(): void
    {
        $holder = new AmqpMessageHolder();

        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects(self::once())
            ->method('basic_ack');

        $configurator = $this->createMock(ChannelProviderInterface::class);
        $configurator->expects(self::once())
            ->method('getChannel')
            ->willReturn($channel);

        $fetcher = new AmqpMessageFetcher($configurator, $holder);

        $fetcher->acknowledge(new QueueMessage('1', 'test'));
    }

    public function testShouldThrowOnAcknowledgeException(): void
    {
        $holder = new AmqpMessageHolder();

        $configurator = $this->createMock(ChannelProviderInterface::class);
        $configurator->expects(self::once())
            ->method('getChannel')
            ->willThrowException(new Exception());

        $fetcher = new AmqpMessageFetcher($configurator, $holder);

        $this->expectException(ConsumerException::class);

        $fetcher->acknowledge(new QueueMessage('1', 'test'));
    }
}
