<?php

namespace Helpers;

class Config
{
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? getenv($key);
        return ($value === false || $value === null || $value === '') ? $default : $value;
    }

    public static function requireSecret(string $key): string
    {
        $value = self::get($key);
        if ($value === null || strlen($value) < 16) {
            throw new \RuntimeException("Missing required secret: {$key}");
        }
        return $value;
    }
}
