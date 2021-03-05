<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange\Callback;

use Qlimix\Queue\Exchange\AmqpNegativeAcknowledgeInterface;

final class NackCallback
{
    private AmqpNegativeAcknowledgeInterface $negativeAcknowledge;

    public function __construct(AmqpNegativeAcknowledgeInterface $negativeAcknowledge)
    {
        $this->negativeAcknowledge = $negativeAcknowledge;
    }

    public function callback(): void
    {
        $this->negativeAcknowledge->nack();
    }
}
