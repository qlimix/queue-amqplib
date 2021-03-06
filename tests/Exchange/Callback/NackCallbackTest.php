<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Exchange\Callback;

use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Exchange\AmqpNegativeAcknowledge;
use Qlimix\Queue\Exchange\Callback\NackCallback;

final class NackCallbackTest extends TestCase
{
    public function testShouldCallback(): void
    {
        $messageHolder = new AmqpNegativeAcknowledge();

        $callback = new NackCallback($messageHolder);
        $callback->callback();

        self::assertTrue($messageHolder->has());
    }
}
