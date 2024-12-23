<?php

namespace AgroZamin\Integration;

use Psr\Http\Message\ResponseInterface;
use Throwable;
use Yiisoft\Arrays\ArrayableInterface;
use Yiisoft\Arrays\ArrayableTrait;

class RequestData implements ArrayableInterface {
    use ArrayableTrait;
    
    public string $serviceName;
    public string $requestId;
    public RequestModel $requestModel;
    public array $options;
    public ResponseInterface|null $response = null;
    public string|null $body;
    public Throwable $exception;

    /**
     * @return array
     */
    public function logData(): array {
        $log['serviceName'] = $this->serviceName;

        $requestBody = $this->requestModel->body();

        if ($requestBody instanceof Payload) {
            $requestBody = $requestBody->toArray();
        }

        $log['request'] = [
            'id' => $this->requestId,
            'url' => $this->requestModel->url(),
            'method' => $this->requestModel->requestMethod(),
            'headers' => $this->requestModel->headers(),
            'queryPath' => $this->requestModel->queryPath(),
            'queryParams' => $this->requestModel->queryParams(),
            'option' => $this->requestModel->requestOption(),
            'body' => $requestBody
        ];

        if (!is_null($this->response)) {
            $log['response'] = [
                'status' => $this->response->getStatusCode(),
                'headers' => $this->response->getHeaders(),
                'body' => $this->body,
            ];
        }

        if (isset($this->exception)) {
            $log['exception'] = [
                'class' => $this->exception::class,
                'message' => $this->exception->getMessage(),
                'code' => $this->exception->getCode(),
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
            ];
        }

        return $log;
    }
}