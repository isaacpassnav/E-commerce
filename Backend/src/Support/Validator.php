<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\ValidationException;

final class Validator
{
    /**
     * @param array<string,mixed> $data
     * @param string[] $required
     */
    public static function require(array $data, array $required): void
    {
        foreach ($required as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === null) {
                throw new ValidationException("El campo '$field' es obligatorio");
            }
        }
    }

    public static function int(mixed $value, string $field, int $min = 1): int
    {
        if (!is_numeric($value)) {
            throw new ValidationException("El campo '$field' debe ser numérico");
        }

        $intValue = (int) $value;
        if ($intValue < $min) {
            throw new ValidationException("El campo '$field' debe ser mayor o igual a $min");
        }

        return $intValue;
    }

    public static function float(mixed $value, string $field, float $min = 0): float
    {
        if (!is_numeric($value)) {
            throw new ValidationException("El campo '$field' debe ser numérico");
        }

        $floatValue = (float) $value;
        if ($floatValue < $min) {
            throw new ValidationException("El campo '$field' debe ser mayor o igual a $min");
        }

        return $floatValue;
    }

    public static function email(mixed $value, string $field): string
    {
        $stringValue = trim((string) $value);
        if (!filter_var($stringValue, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("El campo '$field' no es un email válido");
        }

        return $stringValue;
    }

    /**
     * @param string[] $allowed
     */
    public static function oneOf(mixed $value, string $field, array $allowed): string
    {
        $stringValue = trim((string) $value);
        if (!in_array($stringValue, $allowed, true)) {
            throw new ValidationException("El campo '$field' no es válido");
        }

        return $stringValue;
    }

    public static function datetime(mixed $value, string $field): string
    {
        $stringValue = trim((string) $value);
        $dt = date_create($stringValue);
        if ($dt === false) {
            throw new ValidationException("El campo '$field' debe ser una fecha válida");
        }

        return $dt->format('Y-m-d H:i:s');
    }
}
