<?php

namespace Okvpn\Bundle\RedisQueueBundle\Transport\Semaphore;

/**
 * @internal
 */
final class WindowsSemaphore implements SemaphoreInterface
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
