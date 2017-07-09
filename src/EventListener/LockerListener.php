<?php

namespace Okvpn\Bundle\RedisQueueBundle\EventListener;

use Okvpn\Bundle\RedisQueueBundle\Consumption\LockManagerInterface;

class LockerListener
{
    /** @var LockManagerInterface */
    protected $lockManager;

    /**
     * @param LockManagerInterface $lockManager
     */
    public function __construct(LockManagerInterface $lockManager)
    {
        $this->lockManager = $lockManager;
    }

    public function lockProcess()
    {
        $this->lockManager->lock();
    }
}
