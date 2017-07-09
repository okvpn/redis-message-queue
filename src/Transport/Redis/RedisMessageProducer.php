<?php

namespace Okvpn\Bundle\RedisQueueBundle\Transport\Redis;

use Okvpn\Bundle\RedisQueueBundle\Transport\Redis\Driver\RedisConnection;
use Oro\Component\MessageQueue\Transport\DestinationInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception;
use Oro\Component\MessageQueue\Transport\Exception\InvalidMessageException;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\QueueInterface;
use Oro\Component\MessageQueue\Transport\TopicInterface;
use Oro\Component\MessageQueue\Util\JSON;

class RedisMessageProducer implements MessageProducerInterface
{
    /** @var RedisConnection */
    protected $connection;

    /**
     * @param RedisConnection $connection
     */
    public function __construct(RedisConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     *
     * @param RedisMessage $message
     */
    public function send(DestinationInterface $destination, MessageInterface $message)
    {
        $body = $message->getBody();
        if (is_scalar($body) || is_null($body)) {
            $body = (string) $body;
        } else {
            throw new InvalidMessageException(sprintf(
                'The message body must be a scalar or null. Got: %s',
                is_object($body) ? get_class($body) : gettype($body)
            ));
        }

        if ($destination instanceof TopicInterface) {
            $name = $destination->getTopicName();
        } elseif ($destination instanceof QueueInterface) {
            $name = $destination->getQueueName();
        } else {
            throw new Exception('The "destination" must be instance of TopicInterface or QueueInterface');
        }

        try {
            $rMessage = [
                'body' => $body,
                'headers' => $message->getHeaders(),
                'properties' => $message->getProperties(),
                'process_id' => $this->connection->getProcessId(),
            ];

            $connection = $this->connection->getRedisConnection();
            if ($message->getDelay() !== null) {
                $connection->zAdd(
                    $this->connection->getSetsName($name),
                    $message->getDelay(),
                    JSON::encode($rMessage)
                );
            } else {
                $connection->lPush(
                    $this->connection->getListName($name, $message->getPriority()),
                    JSON::encode($rMessage)
                );
            }
        } catch (\Exception $e) {
            throw new Exception('The transport fails to send the message due to some internal error.', null, $e);
        }
    }
}
