<?php declare(strict_types=1);

namespace Qlimix\Queue\Channel;

use PhpAmqpLib\Channel\AMQPChannel;
use Qlimix\Queue\Channel\Exception\ChannelProviderException;

interface ChannelProviderInterface
{
    /**
     * @throws ChannelProviderException
     */
    public function getChannel(): AMQPChannel;
}
