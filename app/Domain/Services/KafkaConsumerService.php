<?php
/**
 * Created by PhpStorm.
 * User: Woxapp
 * Date: 17.10.2018
 * Time: 10:08
 */

namespace Woxapp\Scaffold\Domain\Services;

use Phalcon\Config;

class KafkaConsumerService
{
    /** @var \Kafka\Consumer */
    protected $consumer;

    /** @var \Kafka\ConsumerConfig */
    protected $config;

    public function __construct(Config $appConfig)
    {
        $this->config = \Kafka\ConsumerConfig::getInstance();
        $this->config->setMetadataRefreshIntervalMs(10000);
        $this->config->setMetadataBrokerList($appConfig->path('external.kafka.host') . ':' . $appConfig->path('external.kafka.port'));

        $this->config->setBrokerVersion($appConfig->path('external.kafka.version'));

        $this->consumer = new \Kafka\Consumer();
    }

    public function setGroupId($groupId)
    {
        $this->config->setGroupId($groupId);
        return $this;
    }

    public function setTopics($topics)
    {
        $this->config->setTopics($topics);
        return $this;
    }

    /**
     * @param \Closure $function
     * closure arguments $topic, $part, $message
     */
    public function start(\Closure $function): void
    {
        $this->consumer->start($function);
    }
}