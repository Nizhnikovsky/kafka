<?php

namespace Woxapp\Scaffold\Domain\Interfaces;

use Phalcon\Di\InjectionAwareInterface;

interface InteractorInterface extends InjectionAwareInterface
{
    /**
     * @param array $headers
     * @param array $body
     * @param array $placeholders
     * @return mixed
     */
    public function process(array $headers, array $body, array $placeholders);

    /**
     * @return mixed
     */
    public function output();
}
