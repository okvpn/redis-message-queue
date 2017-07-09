<?php

namespace Okvpn\Bundle\RedisQueueBundle\Semaphore;

/**
 * @internal
 */
final class RedisSemaphore implements SemaphoreInterface
{
    /** @var \Redis */
    public $redis;

    /**
     * @param \Redis $redis
     */
    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * {@inheritdoc}
     */
    public function acquire($key)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function release($key)
    {
    }
}
