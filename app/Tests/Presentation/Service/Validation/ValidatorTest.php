<?php
/**
 * Created by PhpStorm.
 * User: Woxapp
 * Date: 01.10.2018
 * Time: 16:39
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /** @var Woxapp\Scaffold\Presentation\Service\Validation\Validator */
    protected $validator;

    protected $rules = [];

    public function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->validator = new \Woxapp\Scaffold\Presentation\Service\Validation\Validator();

        $this->rules = [
            'required_headers' => ['Authorization', 'count'],
            'required_params' => ['message'],
            'rules' => [
                'message' => new Symfony\Component\Validator\Constraints\Length(['min' => 1, 'max' => 255]),
                'phone' => new Symfony\Component\Validator\Constraints\Length(['min' => 1, 'max' => 15]),
                'count' => new \Symfony\Component\Validator\Constraints\LessThanOrEqual(7)
            ],
        ];
    }

    /**
     * @expectedExceptionCode 4
     * @expectedException Woxapp\Restful\Presentation\Exception\PresentationException
     */
    public function testHeaderRequiredError()
    {
        $headers = ['Authorization' => 'Bearer 12319381'];
        $this->validator->validateHeaders($headers, $this->rules['required_headers'], $this->rules['rules']);
    }

    /**
     * @expectedExceptionCode 5
     * @expectedException Woxapp\Restful\Presentation\Exception\PresentationException
     */
    public function testHeaderNotValid()
    {
        $headers = ['Authorization' => 'Bearer 12319381', 'count' => 10];
        $this->validator->validateHeaders($headers, $this->rules['required_headers'], $this->rules['rules']);
    }

    /**
     * @expectedExceptionCode 3
     * @expectedException Woxapp\Restful\Presentation\Exception\PresentationException
     */
    public function testHeaderUnknown()
    {
        $headers = ['Authorization' => 'Bearer 12319381', 'count' => 5, 'amount' => 40];
        $this->validator->validateHeaders($headers, $this->rules['required_headers'], $this->rules['rules']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNotValidRule()
    {
        $mockRule = $this->getMockBuilder(stdClass::class)->getMock();
        $headers = ['Authorization' => 'Bearer 12319381', 'count' => 10];
        $this->validator->validateHeaders($headers, $this->rules['required_headers'], ['count' => $mockRule]);
    }

    /** Body cases tests */


    /**
     * @expectedExceptionCode 7
     * @expectedException Woxapp\Restful\Presentation\Exception\PresentationException
     */
    public function testBodyRequiredError()
    {
        $body = ['phone' => '+3806333333333'];
        $this->validator->validateBody($body, $this->rules['required_params'], $this->rules['rules']);
    }

    /**
     * @expectedExceptionCode 8
     * @expectedException Woxapp\Restful\Presentation\Exception\PresentationException
     */
    public function testBodyNotValid()
    {
        $body = ['message' => 'test message', 'phone' => '+38063333333336666'];
        $this->validator->validateBody($body, $this->rules['required_params'], $this->rules['rules']);
    }

    /**
     * @expectedExceptionCode 6
     * @expectedException Woxapp\Restful\Presentation\Exception\PresentationException
     */
    public function testBodyUnknown()
    {
        $body = ['amount' => 40];
        $this->validator->validateBody($body, $this->rules['required_params'], $this->rules['rules']);
    }
}