<?php
/**
 * Created by PhpStorm.
 * User: Woxapp
 * Date: 17.10.2018
 * Time: 10:08
 */

namespace Woxapp\Scaffold\Domain\Services;

use Phalcon\Config;

class KafkaProducerService
{
    /** @var \Kafka\Producer */
    protected $producer;

    public function __construct(Config $appConfig)
    {
        $config = \Kafka\ProducerConfig::getInstance();
        $config->setMetadataRefreshIntervalMs(10000);
        $config->setMetadataBrokerList($appConfig->path('external.kafka.host') . ':' . $appConfig->path('external.kafka.port'));
        $config->setBrokerVersion($appConfig->path('external.kafka.version'));
        $config->setRequiredAck(1);
        $config->setIsAsyn(false);
        $config->setProduceInterval(500);
        $this->producer = new \Kafka\Producer();
    }

    public function sendMessage(string $topic, $value, $key = ''): void
    {
        $this->producer->send([
            [
                'topic' => $topic,
                'value' => $value,
                'key' => $key,
            ],
        ]);
    }
}