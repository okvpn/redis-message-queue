<?php

namespace Okvpn\Bundle\RedisQueueBundle\Client;

use Okvpn\Bundle\RedisQueueBundle\Transport\Redis\RedisMessage;
use Okvpn\Bundle\RedisQueueBundle\Transport\Redis\RedisSession;

use Oro\Component\MessageQueue\Client\Config;
use Oro\Component\MessageQueue\Client\DriverInterface;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Transport\QueueInterface;

class RedisDriver implements DriverInterface
{
    /** @var RedisSession */
    protected $session;

    /** @var Config */
    protected $config;

    /**
     * @var array
     */
    protected $priorityMap;

    /**
     * @param RedisSession $session
     * @param Config $config
     */
    public function __construct(RedisSession $session, Config $config)
    {
        $this->session = $session;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function send(QueueInterface $queue, Message $message)
    {
        $headers = $message->getHeaders();
        $properties = $message->getProperties();

        $headers['content_type'] = $message->getContentType();

        $transportMessage = $this->createTransportMessage();
        $transportMessage->setBody($message->getBody());
        $transportMessage->setHeaders($headers);
        $transportMessage->setProperties($properties);

        $transportMessage->setMessageId($message->getMessageId());
        $transportMessage->setTimestamp($message->getTimestamp());
        $transportMessage->setExpire($message->getExpire());


        $this->session->createProducer()->send($queue, $transportMessage);
    }

    /**
     * @param string $queueName
     *
     * @return QueueInterface
     */
    public function createQueue($queueName)
    {
        $queue = $this->session->createQueue($queueName);

        return $queue;
    }

    /**
     * {@inheritdoc}
     *
     * @return RedisMessage
     */
    public function createTransportMessage()
    {
        return $this->session->createMessage();
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}
