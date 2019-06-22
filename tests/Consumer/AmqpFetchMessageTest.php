<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Consumer;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Channel\ChannelProviderInterface;
use Qlimix\Queue\Consumer\AmqpMessageFetcher;
use Qlimix\Queue\Consumer\AmqpMessageHolderInterface;
use Qlimix\Queue\Consumer\Exception\ConsumerException;
use Qlimix\Queue\Queue\QueueMessage;

final class AmqpFetchMessageTest extends TestCase
{
    /**
     * @test
     */
    public function shouldFetchMessage(): void
    {
        $holder = $this->createMock(AmqpMessageHolderInterface::class);

        $holder->expects($this->once())
            ->method('empty')
            ->willReturn([]);

        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->once())
            ->method('wait');

        $configurator = $this->createMock(ChannelProviderInterface::class);
        $configurator->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $fetcher = new AmqpMessageFetcher($configurator, $holder);

        $fetched = $fetcher->fetch();

        $this->assertSame([], $fetched);
    }

    /**
     * @test
     */
    public function shouldFetchMessageOnTimeoutException(): void
    {
        $holder = $this->createMock(AmqpMessageHolderInterface::class);

        $holder->expects($this->once())
            ->method('empty')
            ->willReturn([]);

        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->once())
            ->method('wait')
            ->willThrowException(new AMQPTimeoutException());

        $configurator = $this->createMock(ChannelProviderInterface::class);
        $configurator->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $fetcher = new AmqpMessageFetcher($configurator, $holder);

        $fetched = $fetcher->fetch();

        $this->assertSame([], $fetched);
    }

    /**
     * @test
     */
    public function shouldThrowOnConnectionException(): void
    {
        $holder = $this->createMock(AmqpMessageHolderInterface::class);

        $configurator = $this->createMock(ChannelProviderInterface::class);
        $configurator->expects($this->once())
            ->method('getChannel')
            ->willThrowException(new Exception());

        $fetcher = new AmqpMessageFetcher($configurator, $holder);

        $this->expectException(ConsumerException::class);

        $fetcher->fetch();
    }

    /**
     * @test
     */
    public function shouldAcknowledge(): void
    {
        $holder = $this->createMock(AmqpMessageHolderInterface::class);

        $channel = $this->createMock(AMQPChannel::class);

        $channel->expects($this->once())
            ->method('basic_ack');

        $configurator = $this->createMock(ChannelProviderInterface::class);
        $configurator->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $fetcher = new AmqpMessageFetcher($configurator, $holder);

        $fetcher->acknowledge(new QueueMessage('1', 'test'));
    }

    /**
     * @test
     */
    public function shouldThrowOnAcknowledgeException(): void
    {
        $holder = $this->createMock(AmqpMessageHolderInterface::class);

        $configurator = $this->createMock(ChannelProviderInterface::class);
        $configurator->expects($this->once())
            ->method('getChannel')
            ->willThrowException(new Exception());

        $fetcher = new AmqpMessageFetcher($configurator, $holder);

        $this->expectException(ConsumerException::class);

        $fetcher->acknowledge(new QueueMessage('1', 'test'));
    }
}
