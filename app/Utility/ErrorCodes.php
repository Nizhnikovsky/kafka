<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 27/06/2017
 * Time: 14:33
 */

namespace Woxapp\Scaffold\Utility;

/**
 * Class ErrorCodes. Constants that are predefined here are used for Validation, change this with caution.
 * @author Rostislav Shaganenko <foobar76239@gmail.com>
 * @package Woxapp\Restful\Utility
 */
class ErrorCodes
{
    const INTERNAL_SERVER_ERROR = ['status' => 500, 'code' => 0, 'message' => 'Internal server error.'];
    const NOT_FOUND = [
        'status' => 404,
        'code' => 1,
        'message' => 'Requested resource were not found at given endpoint.'
    ];
    const INCORRECT_KEY = ['status' => 401, 'code' => 2, 'message' => 'Authentication key: \'%s\' is incorrect.'];
    const REQUEST_HEADERS_UNKNOWN = [
        'status' => 400,
        'code' => 3,
        'message' => 'Incorrect request headers. Headers: \'%s\' are unknown.'
    ];
    const REQUEST_HEADERS_REQUIRED = [
        'status' => 400,
        'code' => 4,
        'message' => 'Incorrect request headers. Headers: \'%s\' are required.'
    ];
    const REQUEST_HEADERS_MALFORMED = [
        'status' => 400,
        'code' => 5,
        'message' => 'Incorrect request headers. Headers: \'%s\' are malformed or incorrect.'
    ];
    const REQUEST_BODY_UNKNOWN = [
        'status' => 400,
        'code' => 6,
        'message' => 'Incorrect request body. Parameters: \'%s\' are unknown.'
    ];
    const REQUEST_BODY_REQUIRED = [
        'status' => 400,
        'code' => 7,
        'message' => 'Incorrect request body. Parameters: \'%s\' are required.'
    ];
    const REQUEST_BODY_MALFORMED = [
        'status' => 400,
        'code' => 8,
        'message' => 'Incorrect request body. Parameters: \'%s\' are malformed or incorrect.'
    ];
    const AUTHENTICATION_FAILED = [
        'status' => 400,
        'code' => 9,
        'message' => 'Either your user session has expired, or your access credentials is malformed.'
    ];
}
