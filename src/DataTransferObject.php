<?php

namespace Mass6\FlexibleDTO;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Mass6\FlexibleDTO\Casts\CastFactory;
use Mass6\FlexibleDTO\Validation\ValidatesProperties;

/**
 * Class DataTransferObject.
 */
abstract class DataTransferObject
{
    /**
     * The DTO data.
     */
    protected array $data;

    /**
     * The original data before cleansing..
     */
    protected array $original;

    /**
     * Indicates if properties invalid properties should be silently ignored.
     *
     * @var bool
     */
    protected bool $ignoreNonPermittedProperties = false;

    /**
     * Indicates if properties should be case sensitive.
     *
     * @var bool
     */
    protected bool $caseSensitive = true;

    /**
     * The properties that should be cast.
     *
     * @var array
     */
    protected array $casts = [];

    public function __construct($data = null, ...$args)
    {
        $this->initializeDataWithNullValues();
        if (isset($data)) {
            $this->setPropertyValuesFromInput($data, $args);
            $this->handleValidation($this->data);
        }
    }

    /**
     * Initialize data array by setting all the allowed properties to null.
     */
    protected function initializeDataWithNullValues(): void
    {
        $this->data = Collection::make($this->allowedProperties())->mapWithKeys(fn ($value) => [$value => null])->toArray();
    }

    /**
     * Return the whitelisted allowed properties.
     */
    abstract protected function allowedProperties(): array;

    /**
     * Set the data properties from cosntructor input.
     *
     * @param  mixed  $data
     * @param  array  $args
     */
    protected function setPropertyValuesFromInput($data, array $args)
    {
        if (is_iterable($data) && empty($args)) {
            $this->setPropertiesFromIterable($data);
        } else {
            $this->setDataFromArguments(array_merge(Arr::wrap($data), $args));
        }
    }

    /**
     * Use the provided array or collection to set the property values.
     *
     * @param  array  $data
     */
    protected function setPropertiesFromIterable(iterable $data)
    {
        collect($data)->each(function ($value, $propertyName) {
            $this->setProperty($propertyName, $value);
        });
    }

    /**
     * Cast a whitelisted property value, cast to the specific type if specified.
     *
     * @param  string  $propertyName
     * @param  mixed  $value
     */
    protected function setProperty(string $propertyName, $value): void
    {
        $property = $this->getWhitelistedProperty($propertyName);

        if ($property) {
            $this->original[$property] = $value;
            $this->data[$property] = CastFactory::make($property, $value, $this->casts[$property] ?? null);
            $this->data[$property] = $this->castValue($property, $value, $this->casts[$property] ?? null);
        }
    }

    /**
     * Retrieve a whitelisted property by a given name.
     *
     * @param $name
     * @return string|null
     */
    protected function getWhitelistedProperty($name): ?string
    {
        $property = Collection::make($this->allowedProperties())->first(function ($allowedProperty) use ($name) {
            return $this->caseSensitive
                ? $name === $allowedProperty
                : $name === $allowedProperty ||
                Str::snake($name) === Str::snake($allowedProperty) ||
                Str::snake(strtolower($name)) === Str::snake(strtolower($allowedProperty));
        });

        if (! $property && ! $this->ignoreNonPermittedProperties) {
            throw new InvalidArgumentException($name.' is not an allowed property.');
        }

        return $property;
    }

    /**
     * Use the individual constructor parameters to set the property values. Properties will be matched
     * to parameter values based on the order they are listed in the "allowedProperties" method.
     *
     * @param  array  $arguments
     */
    protected function setDataFromArguments(array $arguments)
    {
        $this->original = $arguments;

        $allowedProperties = $this->allowedProperties();
        $allowedPropertiesCount = count($allowedProperties);
        $argumentsCount = count($arguments);
        $max = $argumentsCount <= $allowedPropertiesCount ? $argumentsCount : $allowedPropertiesCount;

        $data = [];
        for ($i = 0; $i < $max; $i++) {
            $data[$allowedProperties[$i]] = $this->castValue($allowedProperties[$i], $arguments[$i], $this->casts[$allowedProperties[$i]] ?? null);
        }

        $this->data = array_merge($this->data, $data);
    }

    /**
     * Returns the full list of property values, previously casted to their appropriate types. Will proxy
     * calls to individual property getters if set.
     *
     * @param  bool  $omitNullProperties
     * @return array
     */
    public function getData(bool $omitNullProperties = false): array
    {
        return collect(array_keys($this->data))->mapWithKeys(function ($property) {
            return [$property => $this->$property()];
        })->when($omitNullProperties, function ($data) {
            return $data->filter();
        })->toArray();
    }

    /**
     * Returns the input data, before any cleansing or casting.
     *
     * @return array
     */
    public function getOriginal(): array
    {
        return $this->original;
    }

    /**
     * Returns the raw data array.
     *
     * @return array
     */
    public function getRaw(): array
    {
        return $this->data;
    }

    /**
     * Proxies call to a property getter method.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return $this->{$name}();
        } elseif (method_exists($this, Str::camel($name))) {
            return $this->{Str::camel($name)}();
        } elseif (property_exists($this, $name)) {
            return $this->{$name};
        } elseif (substr($name, 0, 3) === 'get') {
            $property = Str::camel(substr($name, 3));

            return $this->{$property};
        } else {
            return $this->{$name};
        }
    }

    /**
     * Returns a property if it exists.
     *
     * @return mixed
     */
    public function __get(string $property)
    {
        if (array_key_exists($property, $this->data)) {
            return $this->getCastedValue($property);
        } elseif (array_key_exists(Str::snake($property), $this->data)) {
            return $this->getCastedValue(Str::snake($property));
        } elseif (array_key_exists(Str::camel($property), $this->data)) {
            return $this->getCastedValue(Str::camel($property));
        } else {
            if (! in_array($property, $this->allowedProperties())) {
                throw new InvalidArgumentException($property.' is not a valid property.');
            }
        }
    }

    /**
     * Returns a property value to its designated cast type.
     *
     * @param  string  $property
     * @return array|\Carbon\Carbon|\Illuminate\Support\Collection|mixed|string|null
     */
    protected function getCastedValue(string $property)
    {
        $value = $this->data[$property];

        return $this->castValue($property, $value, $this->casts[$property] ?? null);
    }

    /**
     * Casts a property value to a designated type.
     *
     * @param  string  $property
     * @param  $value
     * @param  null  $type
     * @return array|\Carbon\Carbon|\Illuminate\Support\Collection|mixed|string|null
     */
    protected function castValue(string $property, $value, $type = null)
    {
        return CastFactory::make($property, $value, $type);
    }

    /**
     * Handles validation if trait is present on DTO.
     *
     * @param  iterable  $data
     */
    protected function handleValidation(iterable $data)
    {
        $uses = array_flip(class_uses(static::class));

        if (isset($uses[ValidatesProperties::class])) {
            $this->validate($data);
        }
    }
}
