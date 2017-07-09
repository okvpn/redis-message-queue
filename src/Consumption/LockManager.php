<?php

namespace Okvpn\Bundle\RedisQueueBundle\Consumption;

use Okvpn\Bundle\RedisQueueBundle\Transport\Semaphore\RedisSemaphore;
use Okvpn\Bundle\RedisQueueBundle\Transport\Semaphore\SemaphoreInterface;
use Okvpn\Bundle\RedisQueueBundle\Transport\Semaphore\UnixSemaphore;
use Okvpn\Bundle\RedisQueueBundle\Transport\Semaphore\WindowsSemaphore;
use Snc\RedisBundle\DependencyInjection\Configuration\RedisDsn;

final class LockManager
{
    /** @var SemaphoreInterface */
    protected $semaphore;

    /** @var string */
    protected $processId;

    /**
     * @param SemaphoreInterface $semaphore
     */
    protected function __construct(SemaphoreInterface $semaphore)
    {
        $this->semaphore = $semaphore;
        $this->processId = uniqid('', true);
    }

    /**
     * @param string $redisDsn
     * @return LockManager
     */
    public static function create($redisDsn = null)
    {
        if (function_exists('sem_get')) {
            $semaphore = new UnixSemaphore();
        } elseif ($redisDsn !== null) {
            $redis = RedisFactory::create(new RedisDsn($redisDsn));
            $semaphore = new RedisSemaphore($redis);
        } else {
            $semaphore = new WindowsSemaphore();
        }

        return new self($semaphore);
    }

    /**
     * @param string|null $key
     */
    public function lock($key = null)
    {
        if ($key === null) {
            $key = $this->processId;
        }

        $this->semaphore->acquire($key);
    }

    /**
     * @param string|null $key
     */
    public function unlock($key = null)
    {
        if ($key === null) {
            $key = $this->processId;
        }

        $this->semaphore->release($key);
    }

    public function switchProcess()
    {
        $this->unlock();
        $this->processId = uniqid('', true);
    }
}
