<?php

namespace Okvpn\Bundle\RedisQueueBundle\Consumption;

use Okvpn\Bundle\RedisQueueBundle\Semaphore\UnixSemaphore;
use Snc\RedisBundle\DependencyInjection\Configuration\RedisDsn;
use Okvpn\Bundle\RedisQueueBundle\Semaphore\NullSemaphore;
use Okvpn\Bundle\RedisQueueBundle\Semaphore\RedisSemaphore;

class LockFactory
{
    /** @var null|string */
    protected $redisDsn;

    /**
     * @param null|string $redisDsn
     */
    public function __construct($redisDsn = null)
    {
        $this->redisDsn = $redisDsn;
    }

    /**
     * @return LockManager
     */
    public function create()
    {
        if (function_exists('sem_get')) {
            $semaphore = new UnixSemaphore();
        } elseif ($this->redisDsn !== null) {
            $redis = RedisFactory::create(new RedisDsn($this->redisDsn));
            $semaphore = new RedisSemaphore($redis);
        } else {
            $semaphore = new NullSemaphore();
        }

        return new LockManager($semaphore);
    }
}
