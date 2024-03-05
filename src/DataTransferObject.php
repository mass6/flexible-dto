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
     * Indicates if properties invalid properties should be silently ignored.
     *
     * @var bool
     */
    protected bool $ignoreNonPermittedProperties = false;

    /**
     * Indicates if the properties should be case-sensitive.
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

    public static function make($data = null, ...$args): static
    {
        return new static($data, ...$args);
    }

    public function __construct($data = null, ...$args)
    {
        $this->data = [];
        if (isset($data)) {
            $this->setPropertyValuesFromInput($data, $args);
            $this->handleValidation($this->data);
        }
    }

    /**
     * Return the list of whitelisted properties. If the array contains a single asterisk, all properties are allowed.
     */
    protected function allowedProperties(): array
    {
        return ['*'];
    }

    /**
     * Check if a property exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Return a property if it exists, or return default.
     *
     * @param  string  $key
     * @param  mixed|null  $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->has($key) ? $this->$key : $default;
    }

    /**
     * Returns the original input data.
     *
     * @return array
     */
    public function getOriginal(): array
    {
        return $this->data;
    }

    /**
     * Returns only the properties populated, cast to their appropriate types.
     *
     * @return array
     */
    public function getPopulated(): array
    {
        return collect($this->data)
            ->map(fn ($value, $property) => $this->$property())
            ->toArray();
    }

    /**
     * Returns the full list of property values, cast to their appropriate types.
     * Proxies call to individual property getters if set.
     *
     * @return array
     */
    public function getAll(): array
    {
        return array_merge($this->initializePropertiesValues(), $this->getPopulated());
    }

    /**
     * Initialize data array by setting all the allowed properties to null.
     */
    protected function initializePropertiesValues(): array
    {
        $allowedProperties = $this->allowsAllProperties() ? [] : $this->allowedProperties();

        return Collection::make($allowedProperties)->mapWithKeys(fn ($value) => [$value => null])->toArray();
    }

    /**
     * Set the data properties from constructor input.
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
        if ($this->allowsAllProperties()) {
            $property = $propertyName;
        } else {
            $property = $this->getWhitelistedProperty($propertyName);
        }

        if ($property) {
            $this->data[$property] = $value;
        }
    }

    /**
     * Use the individual constructor parameters to set the property values. Properties will be matched
     * to parameter values based on the order they are listed in the "allowedProperties" method.
     *
     * @param  array  $arguments
     */
    protected function setDataFromArguments(array $arguments)
    {
        $allowedProperties = $this->allowedProperties();
        $allowedPropertiesCount = count($allowedProperties);
        $argumentsCount = count($arguments);
        $max = $argumentsCount <= $allowedPropertiesCount ? $argumentsCount : $allowedPropertiesCount;

        $data = [];
        for ($i = 0; $i < $max; $i++) {
            $data[$allowedProperties[$i]] = $arguments[$i];
        }

        $this->data = $data;
    }

    /**
     * Retrieve a whitelisted property by a given name.
     *
     * @param  $name
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
     * Proxies call to a property getter method.
     *
     * @param  $name
     * @param  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return $this->{$name}();
        } elseif (method_exists($this, Str::camel($name))) {
            return $this->{Str::camel($name)}();
        } elseif (method_exists($this, 'get'.Str::camel($name))) {
            return $this->{'get'.Str::camel($name)}();
        } elseif (substr($name, 0, 3) === 'get') {
            return $this->{Str::camel(substr($name, 3))};
        } elseif (property_exists($this, $name)) {
            return $this->{$name};
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
            if (!$this->allowsAllProperties() && !in_array($property, $this->allowedProperties())) {
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

    /**
     * Determines if all properties are allowed.
     *
     * @return bool
     */
    private function allowsAllProperties(): bool
    {
        return $this->allowedProperties() === ['*'];
    }
}
