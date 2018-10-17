<?php
/**
 * Created by PhpStorm.
 * User: dobrik
 * Date: 1/22/18
 * Time: 1:05 PM
 */

namespace Woxapp\Scaffold\Utility;

use Phalcon\Debug\Dump;

class Dumper
{
    private $dumpData = [];

    private $debugger;

    public static $instance;

    private function __construct(Dump $debugger)
    {
        $this->debugger = $debugger;
    }

    public static function dump(...$args)
    {
        if (self::$instance === null) {
            self::$instance = new self(new Dump());
        }

        return self::$instance->append($args);
    }

    public function detailed()
    {
        $this->debugger->setDetailed(true);
        return $this;
    }

    public function append($data)
    {
        if (!empty($data)) {
            if (is_array($data)) {
                $this->dumpData = array_merge($this->dumpData, $data);
            } else {
                $this->dumpData[] = $data;
            }
        }

        return $this;
    }

    public function get()
    {
        echo $this->debugger->variables($this->dumpData);
        exit();
    }

    public function __toString()
    {
        echo $this->debugger->toJson($this->dumpData);
        exit();
        return '';
    }
}