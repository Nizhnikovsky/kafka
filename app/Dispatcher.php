<?php
/**
 * Created by PhpStorm.
 * User: dobrik
 * Date: 1/24/18
 * Time: 3:03 PM
 */

namespace Woxapp\Scaffold;

use Phalcon\Mvc\Dispatcher as BaseDispatcher;

class Dispatcher extends BaseDispatcher
{
    /**
     * @deprecated
     * override base method for implement Dependency Injection
     *
     * @param mixed $handler
     * @param string $actionMethod
     * @param array|null $params
     * @return mixed|void
     */
    public function callActionMethod($handler, $actionMethod, array $params = null)
    {
        $reflector = new \ReflectionClass($handler);
        $reflectionMethod = $reflector->getMethod($actionMethod);

        $arguments = $reflectionMethod->getParameters();

        foreach ($arguments as $argument) {
            if (($class = $argument->getClass()) !== null) {
                if (class_exists($class->getName())) {
                    $instance = $this->getDI()->getShared($class->getName());
                    if($class->implementsInterface('Phalcon\Di\InjectionAwareInterface')){
                        $instance->setDi($this->getDI());
                    }
                    array_unshift($params, $instance);
                }
            }
        }

        return parent::callActionMethod($handler, $actionMethod, $params);
    }
}