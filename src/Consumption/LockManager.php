<?php

namespace Okvpn\Bundle\RedisQueueBundle\Consumption;

use Okvpn\Bundle\RedisQueueBundle\Semaphore\SemaphoreInterface;

final class LockManager implements LockManagerInterface
{
    /** @var SemaphoreInterface */
    protected $semaphore;

    /** @var string */
    protected $processId;

    /**
     * @param SemaphoreInterface $semaphore
     */
    public function __construct(SemaphoreInterface $semaphore)
    {
        $this->semaphore = $semaphore;
        $this->processId = uniqid('', true);
    }

    /**
     * {@inheritdoc}
     */
    public function lock($key = null)
    {
        if ($key === null) {
            $key = $this->processId;
        }

        $this->semaphore->acquire($key);
    }

    /**
     * {@inheritdoc}
     */
    public function unlock($key = null)
    {
        if ($key === null) {
            $key = $this->processId;
        }

        $this->semaphore->release($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessId()
    {
        return $this->processId;
    }

    public function switchProcess()
    {
        $this->unlock();
        $this->processId = uniqid('', true);
    }
}
