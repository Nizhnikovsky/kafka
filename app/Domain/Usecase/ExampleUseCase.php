<?php

namespace Woxapp\Scaffold\Domain\Usecase;

use Woxapp\Scaffold\Domain\Services\KafkaProducerService;

class ExampleUseCase extends Interactor
{
    public function process(array $headers, array $body, array $placeholders)
    {
        /** @var KafkaProducerService $kafka */
        $kafka = $this->getDI()->get('kafkaProducer');
        
        $kafka->sendMessage('sendEmail', 'Some message','dobrik@woxapp.com');
        return $this->respondSuccess();
    }
}
