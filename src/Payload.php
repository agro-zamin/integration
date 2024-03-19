<?php

namespace AgroZamin\Integration;

use Yiisoft\Arrays\ArrayableInterface;
use Yiisoft\Arrays\ArrayableTrait;

abstract class Payload implements ArrayableInterface {
    use ArrayableTrait;
}