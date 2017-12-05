<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 21/11/2017
 * Time: 10:55 AM
 */

namespace Woxapp\Scaffold\Domain\Usecase;

abstract class Interactor
{
    /**
     * @var mixed
     */
    protected $response;

    /**
     * @var string
     */
    protected $action;

    public function input(string $action, array $headers, array $body, array $placeholders = [])
    {
        $this->action = $action;

        if (!method_exists($this, $action)) {
            throw new \BadMethodCallException("Usage of '{$action}' were not found at this usecase.");
        }

        call_user_func([$this, $action], $headers, $body, $placeholders);
    }

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
