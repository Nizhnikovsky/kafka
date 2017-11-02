<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 27/10/2017
 * Time: 5:15 PM
 */

require(__DIR__ . "/Routes.php");

use Phalcon\Loader;
use Phalcon\Config;
use Phalcon\Config\Adapter\Yaml;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\FactoryDefault;
use Phalcon\Events\Event;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Mvc\Model\MetaDataInterface;
use Phalcon\Mvc\Model\Metadata\Memory;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Events\Manager;

const CACHE_CONFIG = false;

$loader = new Loader();
$basePath = __DIR__;

$loader->registerNamespaces(
    [
        'Woxapp\Scaffold' => "{$basePath}/",
        'Firebase\JWT' => "{$basePath}/../vendor/firebase/php-jwt/src"
    ]
)->register();

$di = new FactoryDefault();

$di->setShared('router', $router);

$di->set('config', function (): Config {
    $cachePath = __DIR__ . "/../cache/config.cache.php";

    switch (true) {
        case (CACHE_CONFIG == false):
            return new Yaml(__DIR__ . '/Configuration/config.yaml');
        case (CACHE_CONFIG == true && file_exists($cachePath)):
            $cached = include $cachePath;
            return (new Config())->merge($cached);
        case (CACHE_CONFIG == true && !file_exists($cachePath)):
            $config = new Yaml(__DIR__ . '/Configuration/config.yaml');
            file_put_contents($cachePath, "<?php return " . var_export($config, true) . ";");
            return $config;
    }
});

$di->set('redis', function (): \Redis {
    $config = $this->getConfig();

    $redis = new Redis();
    $redis->connect($config->path('external.redis.host'), $config->path('external.redis.port'));
    $redis->auth('');

    return $redis;
});

$di->set('memcached', function (): \Memcached {
    $config = $this->getConfig();

    $memcached = new \Memcached();
    $memcached->addServer($config->path('external.memcached.host'), $config->path('external.memcached.port'));

    return $memcached;
});

$di->set('gearman', function (): \GearmanClient {
    $config = $this->getConfig();

    $gearman = new \GearmanClient();

    $gearman->addServer($config->path('external.gearman.host'), $config->path('external.gearman.port'));
    if ($gearman->ping('1') === false) {
        throw new \RuntimeException('Cannot connect to the queue server.');
    }

    return $gearman;
});

$di->set('url', function (): Url {
    $config = $this->getConfig();

    $url = new Url();
    $url->setBaseUri($config->path('application.links.project'));

    return $url;
});

$di->set('view', function (): View {
    $view = new View();
    $view->setDI($this);
    $view->setViewsDir(__DIR__ . '/../view');

    $view->registerEngines([
        '.volt' => function ($view) {
            $volt = new Volt($view, $this);

            $volt->setOptions([
                'compiledPath' => __DIR__ . '/../cache/templates',
                'compiledSeparator' => '_'
            ]);

            return $volt;
        }
    ]);

    return $view;
});

$di->set('queryLogManager', function (): Manager {
    $config = $this->getConfig();
    $eventsManager = new Manager();
    $logger = new File($config->path('application.log.files.query'));

    $eventsManager->attach(
        'db:beforeQuery',
        function (Event $event, Mysql $connection) use ($logger) {
            $logger->log(
                $connection->getSQLStatement(),
                Logger::INFO
            );
        }
    );

    return $eventsManager;
});

$di->setShared('db', function (): Mysql {
    $config = $this->getConfig();
    $logQuery = $config->path('database.master.log-query');

    $params = [
        'host'     => $config->path('database.master.host'),
        'username' => $config->path('database.master.username'),
        'password' => $config->path('database.master.password'),
        'dbname'   => $config->path('database.dbname'),
        'charset'  => 'utf8'
    ];

    $connection = new Mysql($params);

    if ($logQuery === true) {
        $connection->setEventsManager($this->get('queryLogManager'));
    }

    return $connection;
});

$di->setShared('modelsMetadata', function (): MetaDataInterface {
    return new Memory();
});

$di->setShared(
    "dispatcher", function (): Dispatcher {
        $eventsManager = new Manager();

        $eventsManager->attach(
            "dispatch:beforeException",
            function (Event $event, Dispatcher $dispatcher, \Throwable $throwable) {
                $dispatcher->forward(
                    [
                        'namespace' => 'Woxapp\\Scaffold\\Presentation\\Controller',
                        'controller' => "Errors",
                        'action' => "error",
                        'params' => ['throwable' => $throwable]
                    ]
                );

                return false;
            }
        );
        $dispatcher = new Dispatcher();
        $dispatcher->setEventsManager($eventsManager);

        return $dispatcher;
    }
);
