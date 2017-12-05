<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 21/11/2017
 * Time: 1:14 PM
 */

namespace Woxapp\Scaffold\Data;

use Phalcon\Config;
use Phalcon\Db\Adapter\Pdo;
use Phalcon\Di;
use Phalcon\Cache\Backend\Libmemcached;
use Phalcon\Cache\Frontend\None;
use Phalcon\Mvc\Model\MetaData\Memory;
use Phalcon\Mvc\Model\MetaData\Libmemcached as ModelMetaData;
use Phalcon\Mvc\Model\MetaData\Strategy\Introspection;
use Phalcon\Mvc\Model\Manager;

class Repository
{
    /* FIXME: Developer should change this according to the project name. */
    const CACHE_PREFIX = 'scaffold';

    /**
     * @var \Phalcon\Di
     */
    protected $container;

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

    public function __construct(Pdo $db, Config $config, Manager $manager)
    {
        $this->container = new Di();
        $this->config = $config;
        $this->db = $db;
        $this->manager = $manager;

        $this->initializeContainer();
        $this->initializeRepositories();
    }

    /**
     * Used for external tools, that will be needed to work with concrete repositories in the future.
     */
    protected function initializeContainer()
    {
        $config = $this->config;

        $this->container->set('memcached', function () use ($config): Libmemcached {
            return new Libmemcached(
                $this->get('frontend'),
                [
                    'server' => [
                        [
                            "host" => $config->path('external.memcached.host'),
                            "port" => $config->path('external.memcached.port')
                        ]
                    ],
                    'client' => [
                        \Memcached::OPT_HASH => \Memcached::HASH_MD5,
                        \Memcached::OPT_PREFIX_KEY => self::CACHE_PREFIX . "-api.",
                    ],
                    'lifetime' => 86400,
                    'statsKey' => '_PHCM',
                    'prefix' => self::CACHE_PREFIX . '-api'
                ]
            );
        });

        $this->container->set('frontend', function () {
            return new None();
        });

        $this->container->set('redis', function () use ($config) {
            $redis = new \Redis();
            $redis->connect($config->path('external.redis.host'), $config->path('external.redis.port'));

            return $redis;
        });

        $this->container->set('metadataMemcached', function () use ($config) {
            return new ModelMetaData(
                [
                    'server' => [
                        [
                            "host" => $config->path('external.memcached.host'),
                            "port" => $config->path('external.memcached.port')
                        ]
                    ],
                    'client' => [
                        \Memcached::OPT_HASH => \Memcached::HASH_MD5,
                        \Memcached::OPT_PREFIX_KEY => self::CACHE_PREFIX . "-metadata.",
                    ],
                    'lifetime' => 86400,
                    'prefix' => self::CACHE_PREFIX . '-metadata'
                ]
            );
        });

        $this->container->set('metadataMemory', function () use ($config) {
            return new Memory();
        });

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
     * Contains concrete repositories and their dependencies.
     */
    protected function initializeRepositories()
    {
        /*FIXME: Use this method to initialize custom Repositories in your project. */
    }

    /**
     * @param string $repository
     * @return mixed
     */
    public function getRepository(string $repository)
    {
        if (!$this->container->has($repository)) {
            throw new \InvalidArgumentException(
                "Repository with name \"{$repository}\" was not found in dependency injection container."
            );
        }

        return $this->container->get($repository);
    }
}
