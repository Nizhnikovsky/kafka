<?php
/**
 * Created by PhpStorm.
 * User: Woxapp
 * Date: 16.10.2018
 * Time: 16:24
 */

require_once '../Bootstrap.php';

/** @var \Woxapp\Scaffold\Domain\Services\KafkaConsumerService $kafka */
$kafka = $di->get('kafkaConsumer');

/** @var \Woxapp\Scaffold\Domain\Services\SwiftMailerService $mailer */
$mailer = $di->get('mailer');

$kafka->setGroupId('first');
$kafka->setTopics(['sendEmail']);

$kafka->start(function ($topic, $part, $message) use ($mailer) {

    $result = $mailer->sendMessage([$message['message']['key']], $message['message']['value'], 'some body');
    echo $result?'ok':'fail';
    echo PHP_EOL;
});