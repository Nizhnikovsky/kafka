<?php

namespace Woxapp\Scaffold\Domain\Usecase;

use Woxapp\Scaffold\Domain\Interfaces\InteractorInterface;

abstract class Interactor implements InteractorInterface
{
    /**
     * @var mixed
     */
    protected $response;

    public function output()
    {
        if ($this->response === null) {
            throw new \BadMethodCallException('Failed to fetch domain layer response. '
                .'Perhaps handler method was not executed or malfunctioned in some way.');
        }

        return $this->response;
    }

    protected function respondSuccess(): bool
    {
        $this->response = ['success' => true];

        return true;
    }

    public function respond(array $data): bool
    {
        $this->response = $data;

        return true;
    }
}
