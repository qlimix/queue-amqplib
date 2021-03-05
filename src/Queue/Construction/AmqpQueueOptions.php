<?php declare(strict_types=1);

namespace Qlimix\Queue\Queue\Construction;

final class AmqpQueueOptions
{
    private bool $passive;
    private bool $durable;
    private bool $autoDelete;

    /** @var mixed[] */
    private array $options;

    /**
     * @param mixed[] $options
     */
    public function __construct(bool $passive, bool $durable, bool $autoDelete, array $options)
    {
        $this->passive = $passive;
        $this->durable = $durable;
        $this->autoDelete = $autoDelete;
        $this->options = $options;
    }

    public function isPassive(): bool
    {
        return $this->passive;
    }

    public function isDurable(): bool
    {
        return $this->durable;
    }

    public function isAutoDelete(): bool
    {
        return $this->autoDelete;
    }

    /**
     * @return mixed[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
