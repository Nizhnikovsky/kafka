<?php
/**
 * Created by PhpStorm.
 * User: dobrik
 * Date: 12/19/17
 * Time: 10:38 AM
 */

namespace Woxapp\Scaffold\Presentation\Service\Validation\Rules;

use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;

class ExampleRules
{
    public static function createChatRules()
    {
        return [
            'required_headers' => ['Authorization'],
            'required_params' => [],
            'rules' => [
                'Authorization' => new Length(['min' => 1, 'max' => 255]),
                'message' => new Length(['min' => 1, 'max' => 255]),
                'amount' => [
                    new Type(['type' => 'numeric']),
                    new Length(['min' => 1, 'max' => 11]),
                ],
                'is_appeal' => new Choice(['choices' => [0, 1]]),
            ],
        ];
    }
}
