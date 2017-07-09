<?php

namespace Okvpn\Bundle\RedisQueueBundle\Semaphore;

/**
 * @internal
 */
final class NullSemaphore implements SemaphoreInterface
{
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
