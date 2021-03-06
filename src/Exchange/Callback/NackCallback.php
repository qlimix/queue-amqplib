<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange\Callback;

use Qlimix\Queue\Exchange\AmqpNegativeAcknowledge;

final class NackCallback
{
    private AmqpNegativeAcknowledge $negativeAcknowledge;

    public function __construct(AmqpNegativeAcknowledge $negativeAcknowledge)
    {
        $this->negativeAcknowledge = $negativeAcknowledge;
    }

    public function callback(): void
    {
        $this->negativeAcknowledge->nack();
    }
}
