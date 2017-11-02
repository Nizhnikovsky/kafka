<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 27/10/2017
 * Time: 5:48 PM
 */

namespace Woxapp\Scaffold\Presentation\Exception;

use Woxapp\Scaffold\Exception\RESTException;

/**
 * Class PresentationException
 * @author Rostislav Shaganenko <foobar76239@gmail.com>
 * @package Woxapp\Scaffold\Presentation\Exception
 */
class PresentationException extends RESTException
{
    /**
     * @var array
     */
    protected $additionalData = [];

    public function __construct(
        array $errorData,
        array $placeholders = [],
        array $additionalData = [],
        \Throwable $previous = null
    ) {
        parent::__construct($errorData, $placeholders, $previous);
        $this->additionalData = $additionalData;
    }

    /**
     * @return array
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}
