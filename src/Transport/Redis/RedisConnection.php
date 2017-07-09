<?php

namespace Okvpn\Bundle\RedisQueueBundle\Transport\Redis;

use Okvpn\Bundle\RedisQueueBundle\Consumption\LockManagerInterface;
use Okvpn\Bundle\RedisQueueBundle\Transport\Redis\Driver\RedisConnection as BaseConnection;

use Oro\Component\MessageQueue\Transport\ConnectionInterface;

class RedisConnection implements ConnectionInterface
{
    /** @var BaseConnection */
    protected $connection;

    /**
     * @param BaseConnection $connection
     */
    protected function __construct(BaseConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     *
     * @return RedisSession
     */
    public function createSession()
    {
        return new RedisSession($this->connection);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->connection->close();
    }

    /**
     * @param array $config
     * @param LockManagerInterface $lockManager
     * @return static
     */
    public static function createConnection(array $config, LockManagerInterface $lockManager)
    {
        $redisConnection = new BaseConnection($config, $lockManager);

        return new static($redisConnection);
    }
}
