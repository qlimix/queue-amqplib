<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange;

use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Exchange\AmqpFailedMessagesHolder;

final class AmqpFailedMessagesHolderTest extends TestCase
{
    public function testShouldFailMessages(): void
    {
        $failedMessages = new AmqpFailedMessagesHolder();
        $failedMessages->fail('test');

        $this->assertTrue($failedMessages->hasFailed());
    }

    public function testShouldResetNegativeAcknowledge(): void
    {
        $failedMessages = new AmqpFailedMessagesHolder();
        $failedMessages->fail('test');
        $failedMessages->reset();

        $this->assertFalse($failedMessages->hasFailed());
    }

    public function testShouldNotBeNegativelyAcknowledgeByDefault(): void
    {
        $failedMessages = new AmqpFailedMessagesHolder();

        $this->assertFalse($failedMessages->hasFailed());
    }
}
