<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange;

final class AmqpNegativeAcknowledge implements AmqpNegativeAcknowledgeInterface
{
    private bool $nacked = false;

    public function nack(): void
    {
        $this->nacked = true;
    }

    public function has(): bool
    {
        return $this->nacked;
    }

    public function reset(): void
    {
        $this->nacked = false;
    }
}
