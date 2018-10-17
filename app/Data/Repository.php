<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 21/11/2017
 * Time: 1:14 PM
 */

namespace Woxapp\Scaffold\Data;

use Phalcon\Di;
use Phalcon\Di\InjectionAwareInterface;
use Memcached;
use Phalcon\DiInterface;
use Phalcon\Mvc\Model\MetaData\Strategy\Introspection;
use Woxapp\Scaffold\Data\Interfaces\RepositoryInterface;
use Woxapp\Scaffold\Data\Service\CacheService;

class Repository implements InjectionAwareInterface, RepositoryInterface
{
    /* FIXME: Developer should change this according to the project name. */
    const CACHE_PREFIX = 'scaffold';

    /**
     * @var \Phalcon\Di
     */
    protected $container;

    /**
     * @var \Phalcon\Di
     */
    protected $mainContainer;

    /**
     * @var \Phalcon\Config
     */
    protected $config;

    /**
     * @var \Phalcon\Db\Adapter\Pdo
     */
    protected $db;

    /**
     * @var \Phalcon\Mvc\Model\Manager
     */
    protected $manager;

    public function __construct(DiInterface $di)
    {
        $this->container = new Di();

        $this->mainContainer = $di;
        $this->config = $di->getConfig();
        $this->db = $di->get('db');
        $this->manager = $di->get('modelsManager');

        $this->initializeContainer();
    }

    /**
     * Used for external tools, that will be needed to work with concrete repositories in the future.
     */
    protected function initializeContainer()
    {
        $config = $this->config;
        $this->registerMemcached($config);

        $this->container->set('redis', function () use ($config) {
            $redis = new \Redis();
            $redis->connect($config->path('external.redis.host'), $config->path('external.redis.port'));

            return $redis;
        });

        //TODO: check what is it
        $this->container->setShared('modelsMetadata', function () use ($config) {
            $metadataName = ucfirst($config->path('orm.metadata-cache'));

            if (!$this->has("metadata{$metadataName}")) {
                throw new \InvalidArgumentException(
                    "{$metadataName} adapter is not supported by current configuration."
                );
            }

            $metadata = $this->get("metadata{$metadataName}");

            $metadata->setStrategy(
                new Introspection()
            );

            return $metadata;
        });

        $this->container->set('db', $this->db);
        $this->container->set('modelsManager', $this->manager);
    }

    /**
     * @param string $entityClass
     * @return RepositoryInterface
     */
    public function getRepository(string $entityClass): RepositoryInterface
    {
        if ($this->container->has($entityClass)) {
            return $this->container->get($entityClass);
        }

        if (!class_exists($entityClass)) {
            throw new \UnexpectedValueException('Class '.$entityClass.' does not exists.');
        }

        if (!defined("$entityClass::REPOSITORY")) {
            throw new \UnexpectedValueException('Class '.$entityClass.' does not contains constant REPOSITORY.');
        }

        $repositoryClass = $entityClass::REPOSITORY;
        if (!class_exists($repositoryClass)) {
            throw new \UnexpectedValueException('Class '.$repositoryClass.' does not exists.');
        }

        if (!in_array(RepositoryInterface::class, class_implements($repositoryClass), true)) {
            throw new \UnexpectedValueException('Class '.$repositoryClass.' must implements RepositoryInterface.');
        }

        $repository = new $repositoryClass($this->container);

        $this->container->set($entityClass, $repository);

        return $repository;
    }

    protected function registerMemcached($config)
    {
        $this->container->set('memcached', function () use ($config): Memcached {
            $memcached =  new Memcached();
            $memcached->addServer($config->path('external.memcached.host'), $config->path('external.memcached.port'));
            return $memcached;
        });

        $this->container->set('cacheService', function (): CacheService {
            return new CacheService($this->get('memcached'));
        });
    }

    /**
     * Sets the dependency injector
     *
     * @param \Phalcon\DiInterface $dependencyInjector
     */
    public function setDI(DiInterface $dependencyInjector)
    {
        $this->mainContainer = $dependencyInjector;
    }

    /**
     * Returns the internal dependency injector
     *
     * @return \Phalcon\DiInterface
     */
    public function getDI()
    {
        return $this->mainContainer;
    }
}
