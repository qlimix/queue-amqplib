<?php declare(strict_types=1);

namespace Qlimix\Queue\Exchange\Constructor;

final class AMQPExchangeOptions
{
    /** @var bool */
    private $passive;

    /** @var bool */
    private $durable;

    /** @var bool */
    private $autoDelete;

    /** @var mixed[] */
    private $options;

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
