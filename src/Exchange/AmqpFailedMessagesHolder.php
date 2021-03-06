<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange;

use function count;

final class AmqpFailedMessagesHolder
{
    /** @var string[] */
    private array $messageIds = [];

    public function hasFailed(): bool
    {
        return count($this->messageIds) > 0;
    }

    public function reset(): void
    {
        $this->messageIds = [];
    }

    public function fail(string $messageId): void
    {
        $this->messageIds[] = $messageId;
    }
}
