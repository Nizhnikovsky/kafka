<?php

namespace Woxapp\Scaffold\Presentation\Service\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validation;
use Woxapp\Restful\Presentation\Exception\PresentationException;
use Woxapp\Scaffold\Utility\ErrorCodes;
use Woxapp\Scaffold\Utility\HttpHeaders;

class Validator
{
    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private $validator;

    public function __construct()
    {
        $this->validator = Validation::createValidator();
    }

    /**
     * @param array $givenFields
     * @param array $requiredFields
     * @param array $constraints
     * @throws PresentationException
     */
    public function validateHeaders(array $givenFields, array $requiredFields, array $constraints = [])
    {
        $this->checkHeaderFieldsCount(array_keys($givenFields), $requiredFields, array_keys($constraints));
        $this->checkRequiredFields($givenFields, $requiredFields, ErrorCodes::REQUEST_HEADERS_REQUIRED);
        $this->checkFieldsValidationRules($givenFields, $constraints, ErrorCodes::REQUEST_HEADERS_MALFORMED);
    }

    /**
     * @param array $givenFields
     * @param array $requiredFields
     * @param array $constraints
     * @throws PresentationException
     */
    public function validateBody(array $givenFields, array $requiredFields, array $constraints = [])
    {
        $this->checkBodyFieldsCount(array_keys($givenFields), $requiredFields, array_keys($constraints));
        $this->checkRequiredFields($givenFields, $requiredFields, ErrorCodes::REQUEST_BODY_REQUIRED);
        $this->checkFieldsValidationRules($givenFields, $constraints, ErrorCodes::REQUEST_BODY_MALFORMED);
    }

    /**
     * @param array $givenKeys
     * @param array $requiredKeys
     * @param array $constraintKeys
     * @throws PresentationException
     */
    private function checkHeaderFieldsCount(array $givenKeys, array $requiredKeys, array $constraintKeys)
    {
        $keys = $this->getAvailableKeys($requiredKeys, $constraintKeys, 'header');

        if ($diff = array_diff($givenKeys, array_merge($keys, HttpHeaders::PREDEFINED_REQUEST_HEADERS))) {
            throw new PresentationException(ErrorCodes::REQUEST_HEADERS_UNKNOWN, [implode(', ', $diff)]);
        }
    }

    /**
     * @param array $givenKeys
     * @param array $requiredKeys
     * @param array $constraintKeys
     * @throws PresentationException
     */
    private function checkBodyFieldsCount(array $givenKeys, array $requiredKeys, array $constraintKeys)
    {
        $keys = $this->getAvailableKeys($requiredKeys, $constraintKeys);

        if ($diff = array_diff($givenKeys, array_merge($keys))) {
            throw new PresentationException(ErrorCodes::REQUEST_BODY_UNKNOWN, [implode(', ', $diff)]);
        }
    }

    /**
     * @param array $requiredKeys
     * @param array $constraintKeys
     * @param string $type
     * @return array
     */
    private function getAvailableKeys(array $requiredKeys, array $constraintKeys, $type = 'body')
    {
        return array_merge($requiredKeys, array_filter($constraintKeys, function ($key) use ($type) {
            return 0 === strcmp($type == 'body' ? lcfirst($key) : ucfirst($key), $key);
        }));
    }

    /**
     * @param array $givenFields
     * @param array $constraints
     * @param array $error
     * @throws PresentationException
     */
    private function checkFieldsValidationRules(array $givenFields, array $constraints, array $error)
    {
        foreach ($givenFields as $name => $value) {
            if (!isset($constraints[$name])) {
                continue;
            }

            $this->checkFieldConstraints($name, $constraints[$name]);
            $this->printErrors($name, $this->validator->validate($value, $constraints[$name]), $error);
        }
    }

    /**
     * @param array $givenFields
     * @param array $requiredFields
     * @param array $error
     * @throws PresentationException
     */
    private function checkRequiredFields(array $givenFields, array $requiredFields, array $error)
    {
        foreach ($requiredFields as $name) {
            if (!array_key_exists($name, $givenFields) || !$this->isValueDefined($givenFields[$name])) {
                throw new PresentationException($error, [$name]);
            }
        }
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function isValueDefined($value)
    {
        return false !== $value && (!empty($value) || '0' == $value);
    }

    /**
     * @param string $field
     * @param mixed $constraints
     */
    private function checkFieldConstraints($field, $constraints)
    {
        if (!is_array($constraints)) {
            $constraints = [$constraints];
        }

        foreach ($constraints as $constraint) {
            $this->isFieldConstraintValid($field, $constraint);
        }
    }

    /**
     * @param string $field
     * @param mixed $constraint
     * @return bool
     */
    private function isFieldConstraintValid($field, $constraint)
    {
        if (!is_object($constraint) || !$constraint instanceof Constraint) {
            throw new InvalidArgumentException(sprintf(
                '%s has invalid validation rule. Rule %s must extend '.Constraint::class.' class.',
                ucfirst($field),
                is_object($constraint) ? get_class($constraint) : (string)$constraint
            ));
        }

        return true;
    }

    /**
     * @param string $field
     * @param ConstraintViolationListInterface $errors
     * @param array $error
     * @throws PresentationException
     */
    private function printErrors($field, ConstraintViolationListInterface $errors, array $error)
    {
        if ($errors->count()) {
            throw new PresentationException($error, [$field]);
        }
    }
}
