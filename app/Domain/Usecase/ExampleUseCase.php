<?php

namespace Woxapp\Scaffold\Domain\Usecase;

class ExampleUseCase extends Interactor
{
    public function process(array $headers, array $body, array $placeholders)
    {
        return $this->respondSuccess();
    }
}
