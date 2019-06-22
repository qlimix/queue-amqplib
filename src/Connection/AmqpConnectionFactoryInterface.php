<?php declare(strict_types=1);

namespace Qlimix\Queue\Connection;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Qlimix\Queue\Connection\Exception\ConnectionException;

interface AmqpConnectionFactoryInterface
{
    /**
     * @throws ConnectionException
     */
    public function getConnection(): AMQPStreamConnection;
}
