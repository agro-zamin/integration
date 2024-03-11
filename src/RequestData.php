<?php

namespace AgroZamin\Integrations;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class RequestData {
    public string $serviceName;
    public string $requestId;
    public RequestModel $requestModel;
    public array $options;
    public ResponseInterface|null $response = null;
    public Throwable $exception;
}