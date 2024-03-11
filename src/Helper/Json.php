<?php

namespace AgroZamin\Integrations\Helper;

class Json {
    /**
     * @param string $json
     * @param $isArray
     *
     * @return array|object
     */
    public static function decode(string $json, $isArray = true): array|object {
        return json_decode($json, $isArray);
    }

    /**
     * @param array $array
     *
     * @return string
     */
    public static function encode(array $array): string {
        return json_encode($array, JSON_UNESCAPED_UNICODE);
    }
}