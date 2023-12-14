<p align="center"><img src="./art/header.png"></p>

# PHP Client for Mistral.AI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/helgesverre/mistral-php.svg?style=flat-square)](https://packagist.org/packages/helgesverre/mistral-php)
[![Total Downloads](https://img.shields.io/packagist/dt/helgesverre/mistral-php.svg?style=flat-square)](https://packagist.org/packages/helgesverre/mistral-php)

The Mistral.ai PHP Client enables PHP applications to interact with the Mistral.ai API, providing straightforward access
to features like chat completions and text embeddings.

## Installation

You can install the package via composer:

```bash
composer require helgesverre/mistral-php
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="mistral-php-config"
```

This is the contents of the published config file:

```php
return [
    'api_key' => env('MISTRAL_API_KEY'),
    'base_url' => env('MISTRAL_BASE_URL'),
];
```

## Usage

```php
use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Mistral;

$mistral = new Mistral(apiKey: config('mistral.api_key'));

// Models 
$list = $mistral->models()->list();

// Embedding 
$embedding = $mistral->embedding()->create([
    "A string here",
    "Another one here",
]);

// Chat 
$responseChat = $mistral->chat()->create(
    messages:[
        "role" => "user",
        "content" => "Hello world!",
    ],
    model: Model::medium->value, 
    temperature:  0.4,
    maxTokens: 1500,
    safeMode: false
);
```

## Testing

```bash
cp .env.example .env
composer test
composer analyse src
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Disclaimer

Mistral and the Mistral logo are trademarks of Mistral.ai. This package is not affiliated with, endorsed by, or
sponsored by Mistral.ai. All trademarks and registered trademarks are the property of their respective owners.

See [Mistral.AI](https://mistral.ai/terms-of-use/) for more information.
