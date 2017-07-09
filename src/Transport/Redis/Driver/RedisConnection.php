<?php

namespace Okvpn\Bundle\RedisQueueBundle\Transport\Redis\Driver;

use Okvpn\Bundle\RedisQueueBundle\Consumption\LockManagerInterface;
use Okvpn\Bundle\RedisQueueBundle\Consumption\RedisFactory;
use Okvpn\Bundle\RedisQueueBundle\Transport\Redis\RedisSession;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Transport\ConnectionInterface;
use Snc\RedisBundle\DependencyInjection\Configuration\RedisDsn;

class RedisConnection implements ConnectionInterface
{
    /** @var RedisDsn */
    private $dsn;

    /** @var bool */
    private $initialized = false;

    /** @var \Redis */
    private $connection;

    /** @var LockManagerInterface */
    private $lockManager;

    /**
     * @param array $config
     * @param LockManagerInterface $lockManager
     */
    public function __construct(array $config, LockManagerInterface $lockManager)
    {
        $this->dsn = new RedisDsn($config['dsn']);
    }

    /**
     * {@inheritdoc}
     */
    public function createSession()
    {
        return new RedisSession($this);
    }

    /**
     * @return \Redis
     */
    public function getRedisConnection()
    {
        // lazy load
        if (false === $this->initialized) {
            $this->initialize();
        }

        return $this->connection;
    }

    public function close()
    {
        if (true === $this->initialized) {
            $this->connection->close();
        }
    }

    /**
     * @param string $queueName
     * @param int $priority
     * @return string
     */
    public function getListName($queueName, $priority = 0)
    {
        return sprintf('%s.%s', $queueName, $priority);
    }

    /**
     * @param string $queueName
     * @return string
     */
    public function getSetsName($queueName)
    {
        return sprintf('sets.%s', $queueName);
    }

    /**
     * @return array
     */
    public function getPriorityMap()
    {
        return [
            MessagePriority::VERY_HIGH => 4,
            MessagePriority::HIGH => 3,
            MessagePriority::NORMAL => 2,
            MessagePriority::LOW => 1,
            MessagePriority::VERY_LOW => 0,
        ];
    }


    /**
     * @param null|string $key
     * @return bool
     */
    public function lock($key = null)
    {
        return $this->lockManager->lock($key);
    }

    /**
     * @param null|string $key
     */
    public function unlock($key = null)
    {
        $this->lockManager->unlock($key);
    }

    /**
     * @return string
     */
    public function getProcessId()
    {
        return $this->lockManager->getProcessId();
    }

    private function initialize()
    {
        if ($this->initialized === true) {
            return;
        }

        $this->connection = RedisFactory::create($this->dsn);
        $this->initialized = true;
    }
}
