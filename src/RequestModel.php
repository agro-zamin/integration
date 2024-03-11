<?php

namespace AgroZamin\Integrations;

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
     * @return DTO
     */
    abstract public function buildDto(array $data): DTO;

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
     * @return array
     */
    public function body(): array {
        return [];
    }
}