<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 27/10/2017
 * Time: 3:15 PM
 */

namespace Woxapp\Scaffold\Domain\Interfaces;

/**
 * Interface InteractorInterface
 * @author Rostislav Shaganenko <foobar76239@gmail.com>
 * @package Woxapp\Scaffold\Domain\Interfaces
 */
interface InteractorInterface
{

    /**
     * @param string $action
     * @param array $placeholders
     * @param array $headers
     * @param array $body
     * @return mixed
     */
    public function input(string $action, array $headers, array $body, array $placeholders = []);

    /**
     * @return mixed
     */
    public function output();

}
