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
    public Throwable $exception;
}