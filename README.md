# Flexible data transfer objects with validation, property whitelisting, and custom casts

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mass6/flexible-dto.svg?style=flat-square)](https://packagist.org/packages/mass6/flexible-dto)
[![Total Downloads](https://img.shields.io/packagist/dt/mass6/flexible-dto.svg?style=flat-square)](https://packagist.org/packages/mass6/flexible-dto)

The aim of this package is to make it really easy to construct Data Transfer Objects with data properties
constrained to a specified whitelist. Then, you can choose to construct the DTO by passing in
each property individually like a classic DTO, or via an associative array.

## Installation

You can install the package via composer:

```bash
composer require mass6/flexible-dto
```

## Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

# Usage

## Object Construction

To create a new DTO, extend the `Mass6\FlexibleDTO\DTO` class and implement the `allowedProperties()` method.
This method should return an array of strings, where each string is the name of a property that is allowed.
```php
use Mass6\FlexibleDTO\DTO;

class MyDTO extends DataTransferObject
{
    protected function allowedProperties(): array
    {
        return [
            'movie',
            'releaseDate',
            'rating',
        ];
    }
}
```
## Instantiating a DTO
There are three ways to instantiate a DTO.

1. Construct the DTO by passing in an associative array.
```php
use Mass6\FlexibleDTO\DTO;

$myDTO = new MyDTO([
    'movie' => 'The Matrix',
    'releaseDate' => '1999-03-31',
    'rating' => 8.7,
]);
```

2. Construct the DTO by passing in a Collection instance.
```php
use Illuminate\Support\Collection;

$data = new Collection([
    'movie' => 'The Matrix',
    'releaseDate' => '1999-03-31',
    'rating' => 8.7,
]);

$myDTO = new MyDTO($data);
```

3. Construct the DTO by passing in each property individually. Each property must be passed 
in the order specified in the `allowedProperties()` method.
```php
use Mass6\FlexibleDTO\DTO;

$myDTO = new MyDTO('The Matrix', '1999-03-31', 8.7);
```

## Accessing Properties
You can access individual properties of the DTO by using the magic getter methods, which is the name of the property.
```php
$myDTO->movie; // 'The Matrix'
$myDTO->getMovie(); // 'The Matrix'
$myDTO->movie(); // 'The Matrix'
```

### `getAll()`
Returns the full list of property values, including non-populated properties (e.g. null values) cast to their appropriate types.
```php
$myDTO = new MyDTO('The Matrix', '1999-03-31');
$myDTO->getAll(); // ['movie' => 'The Matrix', 'releaseDate' => '1999-03-31', 'rating' => null]
```

### `getPopulated()`
Returns only the properties populated, cast to their appropriate types.
```php
$myDTO = new MyDTO('The Matrix', '1999-03-31');
$myDTO->getPopulated(); // ['movie' => 'The Matrix', 'releaseDate' => '1999-03-31']
```

### `getOriginal()`
Returns the original input data before any casting or validation.
```php
$myDTO = new MyDTO('The Matrix', '1999-03-31');
$myDTO->getOriginal(); // ['movie' => 'The Matrix', 'releaseDate' => '1999-03-31']
```

## Validation
Validation is performed on the input data before it is cast to the appropriate types. To make your DTO self-validating, 
you must use the `Mass6\FlexibleDTO\Validation\ValidatesProperties` trait in your DTO class. If validation fails, 
an `Illuminate\Validation\ValidationException` will be thrown.

### Validation Rules
Validation rules are defined in the `getRules()` method. This method should return an array of rules, 
where the key is the name of the property and the value is the validation rule. Validation rules are defined 
using [Laravel validation rules](https://laravel.com/docs/9.x/validation).

```php
use Mass6\FlexibleDTO\DTO;
use Mass6\FlexibleDTO\Validation\ValidatesProperties;

class MyDTO extends DataTransferObject
{
    use ValidatesProperties;
    
    protected function allowedProperties(): array
    {
        return [
            'movie',
            'releaseDate',
            'rating',
        ];
    }

    protected function getRules(): array
    {
        return [
            'movie' => 'required|string',
            'releaseDate' => 'required|date',
            'rating' => 'required|numeric',
        ];
    }
}
```

### Custom Validation Messages
Custom validation messages can be defined in the `getMessages()` method. This method should return an array of messages, 
where the key is the name of the property and the value is the validation message. Messages are defined using
[Laravel validation messages](https://laravel.com/docs/9.x/validation#customizing-the-error-messages) syntax.

```php
use Mass6\FlexibleDTO\DTO;
use Mass6\FlexibleDTO\Validation\ValidatesProperties;

class MyDTO extends DataTransferObject
{
    use ValidatesProperties;
       
    protected function allowedProperties(): array
    {
        return [
            'movie',
            'releaseDate',
            'rating',
        ];
    }

    protected function getRules(): array
    {
        return [
            'movie' => 'required|string',
            'releaseDate' => 'required|date',
            'rating' => 'required|numeric',
        ];
    }

    protected function getMessages(): array
    {
        return [
            'movie.required' => 'The movie title is required.',
            'releaseDate.required' => 'The release date is required.',
            'rating.required' => 'The rating is required.',
        ];
    }
}
```

## Casting
Casting is performed on the input data after it has been validated. The `$cast` property is used to define the 
casting rules, where the key is the name of the property and the value is the casting rule.
```php
use Mass6\FlexibleDTO\DTO;

class MyDTO extends DataTransferObject
{
    protected $casts = [
        'movie' => 'string',
        'releaseDate' => 'date',
        'rating' => 'float',
    ];
    
    protected function allowedProperties(): array
    {
        return [
            'movie',
            'releaseDate',
            'rating',
        ];
    }    
}
```

### Casting Rules
The following casting rules are available.

#### `array`
Casts the value to an array.

#### `bool`, `boolean`
Casts the value to a boolean.

#### `collection`
Casts the value to an `Illuminate\Support\Collection`.

#### `date`
Casts the value to a `Carbon\Carbon` instance.

#### `double`
Casts the value to a double.

#### `float`
Casts the value to a float.

#### `int`, `integer`
Casts the value to an integer.

#### `string`
Casts the value to a string.

#### `custom`
Casts the value to a custom type. The custom type must implement the `Mass6\FlexibleDTO\CastsProperties` interface.
```php
use Mass6\FlexibleDTO\DTO;

class MyDTO extends DataTransferObject
{
    protected function allowedProperties(): array
    {
        return [
            'movie',
            'releaseDate',
            'rating',
        ];
    }

    protected function getRules(): array
    {
        return [
            'movie' => 'required|string',
            'releaseDate' => 'required|date',
            'rating' => 'required|numeric',
        ];
    }

    protected function cast(): array
    {
        return [
            'movie' => 'string',
            'releaseDate' => 'date',
            'rating' => MyCustomType::class,
        ];
    }
}
```

## Custom Cast Types
Custom cast types can be defined by implementing the `Mass6\FlexibleDTO\CastsProperties` interface. The `cast()` method 
should return the value cast to the appropriate type/format.
```php
use Mass6\FlexibleDTO\Castable;

class MyCustomType implements Castable
{
    public function cast($value)
    {
        return (float) $value;
    }
}
```


