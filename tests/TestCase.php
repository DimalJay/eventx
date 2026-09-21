<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionClass;

abstract class TestCase extends BaseTestCase
{
    /**
     * Read a protected/private property value via reflection.
     */
    protected function prop(object $object, string $name)
    {
        $property = (new ReflectionClass($object))->getProperty($name);
        return $property->getValue($object);
    }

    /**
     * Invoke a protected/private method and return its result.
     */
    protected function invoke(object $object, string $method, array $args = [])
    {
        $reflected = (new ReflectionClass($object))->getMethod($method);
        return $reflected->invoke($object, ...$args);
    }
}