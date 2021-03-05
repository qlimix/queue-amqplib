<?php declare(strict_types=1);

namespace Qlimix\Queue\Connection;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Qlimix\Queue\Connection\Exception\ConnectionException;
use Throwable;

final class AmqpConnectionFactory implements AmqpConnectionFactoryInterface
{
    private string $host;
    private int $port;
    private string $vhost;
    private string $user;
    private string $password;

    private ?AMQPStreamConnection $connection;

    public function __construct(string $host, int $port, string $vhost, string $user, string $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->vhost = $vhost;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @inheritDoc
     */
    public function getConnection(): AMQPStreamConnection
    {
        if ($this->connection === null) {
            try {
                $this->connection = new AMQPStreamConnection(
                    $this->host,
                    (string) $this->port,
                    $this->user,
                    $this->password,
                    $this->vhost
                );
            } catch (Throwable $exception) {
                throw new ConnectionException('Failed to connect to amqp', 0, $exception);
            }
        }

        return $this->connection;
    }
}
