<?php

namespace Woxapp\Scaffold\Domain\Usecase;

use Phalcon\DiInterface;
use Woxapp\Scaffold\Domain\Interfaces\InteractorInterface;

abstract class Interactor implements InteractorInterface
{
    /**
     * @var mixed
     */
    protected $response;

    /** @var DiInterface */
    protected $di;

    public function output()
    {
        if ($this->response === null) {
            throw new \BadMethodCallException('Failed to fetch domain layer response. '
                .'Perhaps handler method was not executed or malfunctioned in some way.');
        }

        return $this->response;
    }

    public function setDI(\Phalcon\DiInterface $dependencyInjector)
    {
        $this->di = $dependencyInjector;
    }

    public function getDI()
    {
        return $this->di;
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
