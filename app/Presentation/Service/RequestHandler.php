<?php

namespace Woxapp\Scaffold\Presentation\Service;

use Phalcon\Http\Request;
use Phalcon\Http\RequestInterface;
use Woxapp\Restful\Presentation\Exception\PresentationException;
use Woxapp\Scaffold\Presentation\Service\Validation\Validator;
use Woxapp\Scaffold\Utility\ErrorCodes;
use Woxapp\Scaffold\Utility\FileUtility;

class RequestHandler
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     * @throws PresentationException
     */
    public function getHeaders(): array
    {
        $headers = $this->request->getHeaders();
        $headers['Authorization'] = $this->parseAuthorizationHeader($headers);

        return $headers;
    }

    public function getBody(): array
    {
        switch ($this->request->getMethod()) {
            case 'GET':
                return $this->getQueryData();
            case 'POST':
                return $this->getPostData();
            case 'PUT':
                return $this->getPutData();
            case 'DELETE':
                return $this->getDeleteData();
        }

        throw new \BadMethodCallException("Could not get request body, method '{$this->request->getMethod()}' is not supported.");
    }

    /**
     * @param array $rules
     * @throws PresentationException
     */
    public function validateRequest(array $rules)
    {
        $validator = new Validator();
        $validator->validateHeaders($this->getHeaders(), $rules['required_headers'] ?? [], $rules['rules'] ?? []);
        $validator->validateBody($this->getBody(), $rules['required_params'] ?? [], $rules['rules'] ?? []);
    }

    private function getQueryData(): array
    {
        $query = $this->request->getQuery();
        unset($query['_url']);

        return $query;
    }

    private function getPostData(): array
    {
        $data = ($this->request->getJsonRawBody() === null) ? $this->request->getPost() : $this->request->getJsonRawBody(true);

        return array_merge_recursive($data, FileUtility::transformFiles());
    }

    private function getPutData(): array
    {
        return ($this->request->getJsonRawBody() === null) ? [] : $this->request->getJsonRawBody(true);
    }

    private function getDeleteData(): array
    {
        return $this->getPutData();
    }

    /**
     * @param array $headers
     * @return array
     * @throws PresentationException
     */
    private function parseAuthorizationHeader(array $headers): array
    {
        if (empty($headers['Authorization'])) {
            throw new PresentationException(ErrorCodes::REQUEST_HEADERS_REQUIRED, ['Authorization']);
        }

        if (!$this->isApiKey($headers['Authorization']) && !$this->isBearerToken($headers['Authorization'])) {
            throw new PresentationException(ErrorCodes::REQUEST_HEADERS_MALFORMED, ['Authorization']);
        }

        [$type, $token] = explode(' ', $headers['Authorization']);

        return [$type => $token];
    }

    private function isApiKey(string $authorizationHeader): bool
    {
        return mb_strpos($authorizationHeader, 'Key') !== false;
    }

    private function isBearerToken(string $authorizationHeader): bool
    {
        return mb_strpos($authorizationHeader, 'Bearer') !== false;
    }
}
