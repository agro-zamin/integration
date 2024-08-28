<?php

namespace AgroZamin\Integration;

abstract class RequestModel {
    /**
     * @return string
     */
    abstract public function requestMethod(): string;

    /**
     * @return string
     */
    abstract public function url(): string;

    /**
     * @param array $data
     *
     * @return mixed
     */
    abstract public function buildDto(array $data): mixed;

    /**
     * @return string
     */
    public function requestOption(): string {
        return 'json';
    }

    /**
     * @return array
     */
    public function headers(): array {
        return [];
    }

    /**
     * @return array
     */
    public function queryParams(): array {
        return [];
    }

    /**
     * @return Payload|array|string
     */
    public function body(): Payload|array|string {
        return [];
    }
}