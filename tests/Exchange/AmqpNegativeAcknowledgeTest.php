<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange;

use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Exchange\AmqpNegativeAcknowledge;

final class AmqpNegativeAcknowledgeTest extends TestCase
{
    /**
     * @test
     */
    public function shouldNegativeAcknowledge(): void
    {
        $negativeAcknowledge = new AmqpNegativeAcknowledge();
        $negativeAcknowledge->nack();

        $this->assertTrue($negativeAcknowledge->has());
    }

    /**
     * @test
     */
    public function shouldResetNegativeAcknowledge(): void
    {
        $negativeAcknowledge = new AmqpNegativeAcknowledge();
        $negativeAcknowledge->nack();
        $negativeAcknowledge->reset();

        $this->assertFalse($negativeAcknowledge->has());
    }

    /**
     * @test
     */
    public function shouldNotBeNegativelyAcknowledgeByDefault(): void
    {
        $negativeAcknowledge = new AmqpNegativeAcknowledge();

        $this->assertFalse($negativeAcknowledge->has());
    }
}
