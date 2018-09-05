<?php declare(strict_types=1);

namespace Qlimix\Queue\Connection;

use PhpAmqpLib\Connection\AMQPStreamConnection;

final class AmqpConnectionFactory
{
    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var string */
    private $vhost;

    /** @var string */
    private $user;

    /** @var string */
    private $password;

    /** @var AMQPStreamConnection */
    private $connection;

    /**
     * @param string $host
     * @param int $port
     * @param string $vhost
     * @param string $user
     * @param string $password
     */
    public function __construct(string $host, int $port, string $vhost, string $user, string $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->vhost = $vhost;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @return AMQPStreamConnection
     */
    public function getConnection(): AMQPStreamConnection
    {
        if ($this->connection === null) {
            $this->connection = new AMQPStreamConnection(
                $this->host,
                $this->port,
                $this->user,
                $this->password,
                $this->vhost
            );
        }

        return $this->connection;
    }
}
