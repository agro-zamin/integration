<?php

namespace AgroZamin\Integrations\Exception;

class Exception extends \Exception {
    /**
     * @return string
     */
    public function getName(): string {
        return 'Exception';
    }
}