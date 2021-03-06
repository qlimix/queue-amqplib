<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange;

use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Exchange\AmqpNegativeAcknowledge;

final class AmqpNegativeAcknowledgeTest extends TestCase
{
    public function testShouldNegativeAcknowledge(): void
    {
        $negativeAcknowledge = new AmqpNegativeAcknowledge();
        $negativeAcknowledge->nack();

        $this->assertTrue($negativeAcknowledge->has());
    }

    public function testShouldResetNegativeAcknowledge(): void
    {
        $negativeAcknowledge = new AmqpNegativeAcknowledge();
        $negativeAcknowledge->nack();
        $negativeAcknowledge->reset();

        $this->assertFalse($negativeAcknowledge->has());
    }

    public function testShouldNotBeNegativelyAcknowledgeByDefault(): void
    {
        $negativeAcknowledge = new AmqpNegativeAcknowledge();

        $this->assertFalse($negativeAcknowledge->has());
    }
}
