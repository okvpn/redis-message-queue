<?php

namespace Okvpn\Bundle\RedisQueueBundle\Extension;

use Okvpn\Bundle\RedisQueueBundle\Consumption\LockManager;
use Okvpn\Bundle\RedisQueueBundle\Consumption\LockManagerInterface;
use Oro\Component\MessageQueue\Consumption\AbstractExtension;
use Oro\Component\MessageQueue\Consumption\Context;

class LockerExtension extends AbstractExtension
{
    /** @var LockManagerInterface|LockManager */
    protected $lockManager;

    /**
     * @param LockManagerInterface $lockManager
     */
    public function __construct(LockManagerInterface $lockManager)
    {
        $this->lockManager = $lockManager;
    }

    /**
     * {@inheritdoc}
     */
    public function onPostReceived(Context $context)
    {
        $this->lockManager->switchProcess();
        $this->lockManager->lock();
    }
}
