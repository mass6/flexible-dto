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
