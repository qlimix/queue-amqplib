<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange;

interface AmqpNegativeAcknowledgeInterface
{
    public function nack(): void;

    public function has(): bool;

    public function reset(): void;
}
