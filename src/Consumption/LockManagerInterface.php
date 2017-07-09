<?php

namespace Okvpn\Bundle\RedisQueueBundle\Consumption;

interface LockManagerInterface
{
    /**
     * @param string|null $key
     */
    public function lock($key = null);

    /**
     * @param string|null $key
     */
    public function unlock($key = null);

    /**
     * @return string
     */
    public function getProcessId();
}
