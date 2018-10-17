<?php

namespace Woxapp\Scaffold\Data\Repository;

use Phalcon\DiInterface;
use Woxapp\Scaffold\Data\Interfaces\RepositoryInterface;

class AbstractRepository implements RepositoryInterface
{
    protected $container;

    public function __construct(DiInterface $container)
    {
        $this->container = $container;
    }
}
