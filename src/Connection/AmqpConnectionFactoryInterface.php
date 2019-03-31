<?php declare(strict_types=1);

namespace Qlimix\Queue\Connection;

use PhpAmqpLib\Connection\AMQPStreamConnection;

interface AmqpConnectionFactoryInterface
{
    public function getConnection(): AMQPStreamConnection;
}
