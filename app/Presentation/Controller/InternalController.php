<?php
/**
 * Created by PhpStorm.
 * User: fq
 * Date: 31/10/2017
 * Time: 12:44 PM
 */

namespace Woxapp\Scaffold\Presentation\Controller;

use Phalcon\Mvc\Controller;

/**
 * Class InternalController
 * @author Rostislav Shaganenko <foobar76239@gmail.com>
 * @package Woxapp\Scaffold\Presentation\Controller
 */
class InternalController extends Controller
{
    const CORS_ALLOW_CREDENTIALS = "true";

    public function optionsAction(array $headers, array $methods)
    {
        $origin = $this->di->get('config')->path('application.links.origin');

        return $this->response->setHeader('Access-Control-Allow-Origin', $origin)
            ->setHeader('Access-Control-Allow-Methods', implode(', ', $methods))
            ->setHeader('Access-Control-Allow-Headers', implode(', ', $headers))
            ->setHeader('Access-Control-Allow-Credentials', self::CORS_ALLOW_CREDENTIALS)
            ->setJsonContent(['available' => true])
            ->setStatusCode(200);
    }
}
