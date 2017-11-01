<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 27/10/2017
 * Time: 5:47 PM
 */

namespace Woxapp\Scaffold\Exception;

use Throwable;

/**
 * Class RESTException
 * @author Rostislav Shaganenko <foobar76239@gmail.com>
 * @package Woxapp\Scaffold\Exception
 */
class RESTException extends \Exception
{
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var int
     */
    protected $apiCode;

    /**
     * @var string
     */
    protected $message;

    public function __construct(array $errorData, array $placeholders = [], Throwable $previous = null)
    {
        $this->statusCode = $errorData['status'];

        $this->apiCode = $errorData['code'];

        $placeholderData = array_merge([$errorData['message']], array_values($placeholders));

        $this->message = (empty($placeholders))
            ? $errorData['message']
            : call_user_func_array('sprintf', $placeholderData);

        parent::__construct($this->message, $this->apiCode, $previous);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return int
     */
    public function getApiCode(): int
    {
        return $this->apiCode;
    }
}
