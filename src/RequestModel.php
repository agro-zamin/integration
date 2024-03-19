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
     * @return DTO|array
     */
    abstract public function buildDto(array $data): DTO|array;

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
     * @return array|string
     */
    public function body(): array|string {
        return [];
    }
}