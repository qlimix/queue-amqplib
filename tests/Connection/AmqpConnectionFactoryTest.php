<?php declare(strict_types=1);

namespace Qlimix\Tests\Queue\Connection;

use PHPUnit\Framework\TestCase;
use Qlimix\Queue\Connection\AmqpConnectionFactory;
use Qlimix\Queue\Connection\Exception\ConnectionException;

final class AmqpConnectionFactoryTest extends TestCase
{
    public function testShouldCreateConnection(): void
    {
        $connectionFactory = new AmqpConnectionFactory(
            'localhost',
            0,
            '/',
            'foo',
            'bar'
        );

        $this->expectException(ConnectionException::class);

        $connectionFactory->getConnection();
    }
}
