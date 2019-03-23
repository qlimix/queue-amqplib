<?php declare(strict_types=1);

namespace Qlimix\Queue\Channel;

use PhpAmqpLib\Channel\AMQPChannel;
use Qlimix\Queue\Channel\Exception\AmqpChannelConfiguratorException;

interface AmqpChannelConfiguratorInterface
{
    /**
     * @throws AmqpChannelConfiguratorException
     */
    public function getChannel(): AMQPChannel;
}
