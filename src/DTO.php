<?php

namespace AgroZamin\Integration;

use AgroZamin\Integration\Helper\ArrayHelper;
use ReflectionClass;
use ReflectionProperty;
use function is_null;

abstract class DTO {
    private array $except = [];

    /**
     * @param array|null $properties
     */
    public function __construct(array|null $properties = []) {
        if (is_null($properties)) {
            return;
        }

        $this->loadProperties($properties);
    }

    /**
     * @return array
     */
    protected function properties(): array {
        return [];
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function loadProperties(array $data): void {
        $attributes = $this->attributes($data);
        $objects = $this->objects($data);

        $properties = array_merge($attributes, $objects);

        foreach ($properties as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function attributes(array $data): array {
        $reflectionClass = new ReflectionClass($this);
        $propertyNames = array_column($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC), 'name');
        return array_intersect_key($data, array_flip($propertyNames));
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function objects(array $data): array {
        $objects = array_diff_key($this->properties(), array_flip($this->except));

        $attributes = [];
        foreach ($objects as $propertyName => $object) {
            $attributes[$propertyName] = $this->getInstance(...$this->configure($object, $data));
        }

        return $attributes;
    }

    /**
     * @param array|string $object
     * @param array $data
     *
     * @return array
     */
    private function configure(array|string $object, array $data): array {
        if (is_string($object)) {
            return [$object, $data];
        }

        $objectClassName = array_shift($object);

        $arguments = [];

        foreach ($object as $param) {
            $arguments[] = $data[$param];
        }

        return [$objectClassName, $arguments];
    }

    /**
     * @param $className
     * @param $data
     *
     * @return array
     */
    protected function arrayableObject($className, $data): array {
        return array_filter(array_map(function ($datum) use ($className) {
            return $this->processDatum($className, $datum);
        }, $data));
    }

    /**
     * @param $className
     * @param $datum
     *
     * @return array|mixed
     */
    private function processDatum($className, $datum): mixed {
        if (ArrayHelper::isAssociative($datum)) {
            return $this->getInstance($className, $datum);
        }

        return array_map(fn($item) => $this->getInstance($className, [$item]), $datum);
    }

    /**
     * @param array|string|callable $className
     * @param array $arguments
     *
     * @return mixed
     */
    protected function getInstance(array|string|callable $className, array $arguments = []): mixed {
        return match (true) {
            is_array($className) => $this->arrayableObject(array_shift($className), $arguments),
            is_callable($className) => $className(...$arguments),
            method_exists($className, 'build') => call_user_func([$className, 'build'], ...$arguments),
            default => new $className(...$arguments)
        };
    }
}