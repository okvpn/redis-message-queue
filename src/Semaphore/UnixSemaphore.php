<?php

namespace Okvpn\Bundle\RedisQueueBundle\Semaphore;

/**
 * @internal
 */
final class UnixSemaphore implements SemaphoreInterface
{
    /** @var array */
    protected $semaphores = [];

    /**
     * {@inheritdoc}
     */
    public function acquire($key)
    {
        $sem = sem_get($this->generateKey($key));
        $this->semaphores[$key] = $sem;

        return sem_acquire($sem, true);
    }

    /**
     * {@inheritdoc}
     */
    public function release($key)
    {
        if (isset($this->semaphores[$key])) {
            $sem = $this->semaphores[$key];
            unset($this->semaphores[$key]);
            @sem_release($sem);
            @sem_remove($sem);
        }
    }

    /**
     * @param string $key
     * @return int
     */
    private function generateKey($key)
    {
        return hexdec(substr(md5($key), 0, 8));
    }
}
