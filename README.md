# Octany API PHP SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/octany/octany-php.svg?style=flat-square)](https://packagist.org/packages/octany/octany-php)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/octany/octany-php/run-tests?label=tests)](https://github.com/octany/octany-php/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/octany/octany-php/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/octany/octany-php/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)

This package is under development

## Installation

You can install the package via composer:

```bash
composer require octany/octany-php
```

## Usage

```php
$client = new Octany\OctanyClient();
$subscriptions = $client->subscriptions()->all();
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
