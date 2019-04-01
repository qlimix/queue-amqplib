<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange;

interface AmqpFailedMessagesHolderInterface
{
    public function fail(string $messageId): void;

    public function hasFailed(): bool;

    public function reset(): void;
}
