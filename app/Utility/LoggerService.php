<?php

namespace Woxapp\Scaffold\Utility;

use Phalcon\Di;

class LoggerService
{
    private static $timer;

    private static $log = [];

    private static $logger = null;

    private static $di = null;

    public static function log($description)
    {
        self::initTimer();

        self::$log[] = [
            'description' => $description,
            'time' => sprintf('%.5F sec.', microtime(true) - self::$timer),
            'memory' => round(memory_get_usage() / 1048576, 2) . ' mb'
        ];
    }

    private static function initTimer()
    {
        if (self::$timer === null) {
            self::$timer = microtime(true);
        }
    }

    private static function initDi()
    {
        if (self::$di === null) {
            self::$di = Di::getDefault();
        }
    }

    private static function initLogger()
    {
        self::initDi();
        if (self::$logger === null) {
            self::$logger = self::$di->get('logger');
        }
    }

    public static function write()
    {
        self::initLogger();
        foreach (self::$log as $logLine)
        {
            self::$logger->alert("description => '{$logLine['description']}', time => '{$logLine['time']}', memory => '{$logLine['memory']}'");
        }

        self::$log = [];
    }

    public static function get()
    {
        var_dump(self::$log);
        exit();
    }

}
