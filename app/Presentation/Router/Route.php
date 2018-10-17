<?php

namespace Woxapp\Scaffold\Presentation\Router;

class Route extends \Phalcon\Mvc\Router\Route
{
    /**
     * @var string
     */
    private $useCaseClass;

    /**
     * @var array
     */
    private $validationRules;

    public function __construct(string $pattern, string $useCaseClass, array $httpMethods = [], array $validationRules = [])
    {
        $this->useCaseClass = $useCaseClass;
        $this->validationRules = $validationRules;

        parent::__construct($pattern, [], $httpMethods);
    }

    public function getUseCaseClass(): ?string
    {
        return $this->useCaseClass;
    }

    public function getValidationRules(): array
    {
        return $this->validationRules;
    }
}
