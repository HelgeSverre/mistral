<p align="center"><img src="./art/header.png"></p>

# Mistral.ai PHP Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/helgesverre/mistral-php.svg?style=flat-square)](https://packagist.org/packages/helgesverre/mistral-php)
[![Total Downloads](https://img.shields.io/packagist/dt/helgesverre/mistral-php.svg?style=flat-square)](https://packagist.org/packages/helgesverre/mistral-php)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/mistral-php.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/mistral-php)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can
support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.
You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards
on [our virtual postcard wall](https://spatie.be/open-source/postcards).

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
$mistral = new HelgeSverre\Mistral();
echo $mistral->echoPhrase('Hello, Helge Sverre!');
```

## Testing

```bash
cp .env.example .env
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
