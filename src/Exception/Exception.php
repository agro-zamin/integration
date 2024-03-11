<?php

namespace AgroZamin\Integration\Exception;

class Exception extends \Exception {
    /**
     * @return string
     */
    public function getName(): string {
        return 'Exception';
    }
}