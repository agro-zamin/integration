<?php

namespace AgroZamin\Integration;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Throwable;

abstract class Integration {
    protected const REQUEST_ID_HEADER_NAME = 'X-Request-Id';
    protected const API_VERSION_HEADER_NAME = 'X-Api-Version';

    private string $requestId;
    private RequestModel $requestModel;

    /**
     * @return string
     */
    abstract protected function serviceName(): string;

    /**
     * @return string
     */
    abstract protected function apiVersion(): string;

    /**
     * @return ClientInterface
     */
    abstract protected function getClient(): ClientInterface;

    /**
     * @param RequestData $requestData
     *
     * @return void
     */
    abstract protected function onBeforeRequest(RequestData $requestData): void;

    /**
     * @param RequestData $requestData
     *
     * @return void
     */
    abstract protected function onAfterRequest(RequestData $requestData): void;

    /**
     * @param RequestData $requestData
     *
     * @return void
     */
    abstract protected function onThrow(RequestData $requestData): void;

    /**
     * @param ResponseInterface $response
     * @param Throwable $exception
     *
     * @return Throwable
     */
    protected function handleException(ResponseInterface $response, Throwable $exception): Throwable {
        return $exception;
    }

    /**
     * @return array
     */
    protected function defaultHeaders(): array {
        return [];
    }

    /**
     * @param RequestModel $requestModel
     *
     * @return $this
     */
    public function requestModel(RequestModel $requestModel): static {
        $this->requestModel = $requestModel;
        return $this;
    }

    /**
     * @return string
     */
    protected function generateRequestId(): string {
        if (!isset($this->requestId)) {
            $this->requestId = Uuid::uuid4()->toString();
        }

        return $this->requestId;
    }

    /**
     * @return mixed
     * @throws Throwable
     * @throws GuzzleException
     * @throws ClientException
     * @throws ServerException
     */
    public function sendRequest(): mixed {
        $options = $this->buildRequestOptions();

        $requestData = new RequestData();

        $requestData->serviceName = $this->serviceName();
        $requestData->requestId = $this->requestId;
        $requestData->requestModel = $this->requestModel;
        $requestData->options = $options;

        $this->onBeforeRequest($requestData);

        try {
            $response = $this->getClient()->request($this->requestModel->requestMethod(), $this->prepareUrl(), $options);
        } catch (ClientException|ServerException $exception) {
            $requestData->exception = $exception;
            $requestData->response = $exception->getResponse();

            $this->onThrow($requestData);

            throw $this->handleException($requestData->response, $exception);
        } catch (Throwable $exception) {
            $requestData->exception = $exception;

            $this->onThrow($requestData);

            throw $exception;
        }

        $requestData->response = $response;
        $requestData->body = $response->getBody()->getContents();

        $this->onAfterRequest($requestData);

        $this->clear();

        $responseBody = $this->requestModel->parseResponseBody($requestData->body);

        return $this->requestModel->buildDto($responseBody);
    }

    /**
     * @return array[]
     */
    protected function buildRequestOptions(): array {
        $options = [
            'headers' => $this->filledHeaders()
        ];

        if (!empty($this->requestModel->queryParams())) {
            $options['query'] = $this->requestModel->queryParams();
        }

        if (empty($this->requestModel->body())) {
            return $options;
        }

        if (strtoupper($this->requestModel->requestMethod()) === 'GET') {
            return $options;
        }

        $body = $this->requestModel->body();

        if ($body instanceof Payload) {
            $body = $body->toArray();
        }

        $requestOption = $this->requestModel->requestOption();

        $options[$requestOption] = $body;

        return $options;
    }

    /**
     * @return string
     */
    private function prepareUrl(): string {
        $url = $this->requestModel->url();

        if (empty($this->requestModel->queryPath())) {
            return $url;
        }

        $pathNames = array_keys($this->requestModel->queryPath());
        $pathValues = array_values($this->requestModel->queryPath());

        return str_replace($pathNames, $pathValues, $url);
    }

    /**
     * @return array
     */
    private function filledHeaders(): array {
        $headers = array_merge($this->defaultHeaders(), $this->requestModel->headers());

        $headers[self::REQUEST_ID_HEADER_NAME] = $this->generateRequestId();
        $headers[self::API_VERSION_HEADER_NAME] = $this->apiVersion();

        return $headers;
    }

    /**
     * @return void
     */
    private function clear(): void {
        unset($this->requestId);
    }
}