<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange;

final class AmqpNegativeAcknowledge implements AmqpNegativeAcknowledgeInterface
{
    /** @var bool */
    private $nacked = false;

    public function nack(): void
    {
        $this->nacked = true;
    }

    public function hasNegativeAcknowledge(): bool
    {
        return $this->nacked;
    }

    public function reset(): void
    {
        $this->nacked = false;
    }
}
