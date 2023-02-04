<?php

namespace Mass6\FlexibleDTO\Casts;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Exception;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class CastFactory
{
    public static function make(string $property, $value, ?string $type = null)
    {
        if (is_subclass_of($type, CastsProperties::class)) {
            return self::getCustomCast($property, $type, $value);
        }

        return self::getCast($property, $value, $type);
    }

    /**
     * @param  string  $property
     * @param  $value
     * @param  string|null  $type
     * @return Carbon|mixed
     */
    protected static function getCast(string $property, $value, ?string $type)
    {
        switch ($type) {
            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'int':
            case 'integer':
                return filter_var($value, FILTER_VALIDATE_INT);
            case 'double':
            case 'float':
                return filter_var($value, FILTER_VALIDATE_FLOAT);
            case 'string':
                return self::castToString($property, $value);
            case 'array':
                return self::castToArray($property, $value);
            case 'date':
                return self::castToDate($property, $value);
            case 'collection':
                return self::castToCollection($property, $value);
            default:
                return $value;
        }
    }

    /**
     * @param  string  $property
     * @param  $type
     * @param  $value
     * @return mixed
     */
    protected static function getCustomCast(string $property, $type, $value)
    {
        $castModel = new $type();

        try {
            return $castModel->cast($value);
        } catch (Exception $e) {
            self::throwExceptionMessage($property, 'type');
        }
    }

    /**
     * @param  string  $property
     * @param  $value
     * @return Carbon
     */
    protected static function castToDate(string $property, $value): ?Carbon
    {
        if (! $value) {
            return $value;
        }

        try {
            return Carbon::parse($value);
        } catch (InvalidFormatException|Exception $e) {
            throw new InvalidArgumentException(sprintf('The provided %s value of `%s` is not a valid date format.', $property, $value));
        }
    }

    protected static function castToCollection(string $property, $array): Collection
    {
        try {
            return Collection::make($array);
        } catch (InvalidFormatException|Exception $e) {
            self::throwExceptionMessage($property, 'array');
        }
    }

    /**
     * @param  string  $property
     * @param  $value
     * @return array
     */
    protected static function castToArray(string $property, $value): array
    {
        if ($value instanceof Collection) {
            return $value->toArray();
        }

        try {
            return (array) $value;
        } catch (Exception $e) {
            self::throwExceptionMessage($property, 'array', $value);
        }
    }

    protected static function castToString(string $property, $value): ?string
    {
        if (self::isNotStringable($value)) {
            self::throwExceptionMessage($property, 'string');
        }

        return strval($value);
    }

    protected static function throwExceptionMessage(string $property, string $type, $value = null)
    {
        if ($value) {
            throw new InvalidArgumentException(sprintf('The provided %s value of `%s` could not be cast to %s.', $property, $value, $type));
        } else {
            throw new InvalidArgumentException(sprintf('The provided %s value could not be cast to %s.', $property, $type));
        }
    }

    /**
     * @param $value
     * @return bool
     */
    protected static function isNotStringable($value): bool
    {
        return
            is_array($value) ||
            (
                is_object($value) &&
                ! method_exists($value, '__toString')
            );
    }
}
