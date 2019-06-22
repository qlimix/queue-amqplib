<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange\Callback;

use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Exchange\AmqpNegativeAcknowledgeInterface;
use Qlimix\Queue\Exchange\Callback\NackCallback;

final class NackCallbackTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCallback(): void
    {
        $messageHolder = $this->createMock(AmqpNegativeAcknowledgeInterface::class);

        $messageHolder->expects($this->once())
            ->method('nack');

        $callback = new NackCallback($messageHolder);
        $callback->callback();
    }
}
