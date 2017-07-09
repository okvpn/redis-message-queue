<?php

namespace Okvpn\Bundle\RedisQueueBundle\Consumption;

use Snc\RedisBundle\DependencyInjection\Configuration\RedisDsn;

final class RedisFactory
{
    /**
     * @param RedisDsn $dsn
     * @return \Redis
     */
    public static function create(RedisDsn $dsn)
    {
        $redis = new \Redis();

        if (null !== $dsn->getSocket()) {
            $redis->connect($dsn->getSocket());
        } else {
            $redis->connect($dsn->getHost(), $dsn->getPort());
        }

        if ('' !== $dsn->getPassword()) {
            $redis->auth($dsn->getPassword());
        }

        if (0 !== $dsn->getDatabase()) {
            $redis->select($dsn->getDatabase());
        }

        return $redis;
    }
}
