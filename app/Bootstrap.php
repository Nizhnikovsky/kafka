<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 27/10/2017
 * Time: 5:15 PM
 */

require(__DIR__ . '/../vendor/autoload.php');

use Phalcon\Config;
use Phalcon\Config\Adapter\Yaml;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\FactoryDefault;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Loader;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Mvc\Model\Manager as ModelsManager;
use Phalcon\Mvc\Url;
use Woxapp\Restful\Presentation\Interfaces\ValidationServiceInterface;
use Woxapp\Restful\Presentation\Service\ValidationService;
use Woxapp\Scaffold\Data\Repository;
use Woxapp\Scaffold\Dispatcher;
use Woxapp\Scaffold\Domain\Services\KafkaConsumerService;
use Woxapp\Scaffold\Domain\Services\KafkaProducerService;
use Woxapp\Scaffold\Presentation\EventListener\CorsListener;

const CACHE_CONFIG = false;

$loader = new Loader();
$basePath = __DIR__;

$loader->registerNamespaces(
    [
        /* FIXME: Developers should change this according to the project. */
        'Woxapp\Scaffold' => "{$basePath}/"
    ]
)->register();

$di = new FactoryDefault();

//Register logger for LoggerService
$di->set('logger', function (): FileAdapter {
    return new FileAdapter(__DIR__ . '/../logs/logs.log');
});

$di->set('config', function (): Config {
    $cachePath = __DIR__ . "/../cache/config.cache.php";

    switch (true) {
        case (CACHE_CONFIG == true && file_exists($cachePath)):
            $cached = include $cachePath;
            return (new Config())->merge($cached);
        case (CACHE_CONFIG == true && !file_exists($cachePath)):
            $config = new Yaml(__DIR__ . '/Configuration/config.yml');
            file_put_contents($cachePath, "<?php return " . var_export($config, true) . ";");
            return $config;
        default:
            return new Yaml(__DIR__ . '/Configuration/config.yml');
    }
});

$di->set('mailer', function (): \Woxapp\Scaffold\Domain\Services\SwiftMailerService {
    return new \Woxapp\Scaffold\Domain\Services\SwiftMailerService($this);
});

$di->setShared('kafkaProducer', function (): KafkaProducerService {
    return new KafkaProducerService($this->getConfig());
});

$di->setShared('kafkaConsumer', function (): KafkaConsumerService {
    return new KafkaConsumerService($this->getConfig());
});

//Register repositories storage
$di->set('repository', function (): Repository {
    return new Repository($this);
});

//Register validation service of presentation layer
$di->set('validation', function (): ValidationServiceInterface {
    return new ValidationService();
});

$di->set('url', function (): Url {
    $config = $this->getConfig();

    $url = new Url();
    $url->setBaseUri($config->path('application.links.project'));

    return $url;
});

$di->set('queryLogManager', function (): EventsManager {
    $config = $this->getConfig();
    $eventsManager = new EventsManager();
    $logger = new FileAdapter($config->path('application.log.files.query'));

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

$di->setShared('modelsManager', function (): ModelsManager {
    $manager = new ModelsManager();
    /* FIXME: Developers should change this according to the project. */
    $manager->registerNamespaceAlias('e', '\Woxapp\Scaffold\Data\Entity');
    return $manager;
});

$di->setShared('db', function (): Mysql {
    $config = $this->getConfig();
    $logQuery = $config->path('database.master.log-query');

    $params = [
        'host' => $config->path('database.master.host'),
        'username' => $config->path('database.master.username'),
        'password' => $config->path('database.master.password'),
        'dbname' => $config->path('database.dbname'),
        'charset' => 'utf8'
    ];

    $connection = new Mysql($params);

    if ($logQuery === true) {
        $connection->setEventsManager($this->get('queryLogManager'));
    }

    return $connection;
});

$di->setShared('corsListener', function () {
    return new CorsListener();
});

$di->setShared("dispatcher", function () use ($di): Dispatcher {
    $eventsManager = new EventsManager();
    $eventsManager->attach("dispatch:beforeExecuteRoute", $di->get('corsListener'));

    $dispatcher = new Dispatcher();
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
});
