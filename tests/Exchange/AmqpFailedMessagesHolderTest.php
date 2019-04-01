<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange;

use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Exchange\AmqpFailedMessagesHolder;

final class AmqpFailedMessagesHolderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldFailMessages(): void
    {
        $failedMessages = new AmqpFailedMessagesHolder();
        $failedMessages->fail('test');

        $this->assertTrue($failedMessages->hasFailed());
    }

    /**
     * @test
     */
    public function shouldResetNegativeAcknowledge(): void
    {
        $failedMessages = new AmqpFailedMessagesHolder();
        $failedMessages->fail('test');
        $failedMessages->reset();

        $this->assertFalse($failedMessages->hasFailed());
    }

    /**
     * @test
     */
    public function shouldNotBeNegativelyAcknowledgeByDefault(): void
    {
        $failedMessages = new AmqpFailedMessagesHolder();

        $this->assertFalse($failedMessages->hasFailed());
    }
}
