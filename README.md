<p align="center"><img src="./art/header.png"></p>

# Laravel Client for Mistral.AI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/helgesverre/mistral.svg?style=flat-square)](https://packagist.org/packages/helgesverre/mistral)
[![Total Downloads](https://img.shields.io/packagist/dt/helgesverre/mistral.svg?style=flat-square)](https://packagist.org/packages/helgesverre/mistral)

The Mistral.ai Laravel Client enables laravel applications to interact with the Mistral.ai API, providing
straightforward access to features like chat completions and text embeddings.

Get your API key at [console.mistral.ai](https://console.mistral.ai/).

## Installation

### Composer Installation

Install the package via composer to integrate it into your Laravel project. This process is straightforward and follows
standard composer package installation procedures.

```bash
composer require helgesverre/mistral
```

### Publishing Configuration

Publish the configuration file to customize settings like the API key and base URL. This step is crucial for tailoring
the client to interact with your specific Mistral.ai environment.

```bash
php artisan vendor:publish --tag="mistral-config"
```

#### Configuration File Contents

This snippet shows the default configuration, which you can modify according to your needs. By default, it uses
environment variables for secure API key storage and sets the base URL to the production API endpoint of Mistral.AI.

```php
return [
    'api_key' => env('MISTRAL_API_KEY'),
    'base_url' => env('MISTRAL_BASE_URL', 'https://api.mistral.ai'),
];
```

## Usage

### Client Instantiation

Create an instance of the Mistral client to start interacting with the API. This instance will be your primary interface
for sending requests to Mistral.AI.

```php
<?php

use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Mistral;

// Instantiate the client
$mistral = new Mistral(apiKey: config('mistral.api_key'));

```

```php
// Or use the facade with laravel
Mistral::chat();
Mistral::simpleChat();
Mistral::embedding();
Mistral::models();

```

## Resources

### `Models` Resource

#### List available models

```php

// Models
$list = $mistral->models()->list();

```

### `Embeddings` Resource

#### Create embedding

```php
$embedding = $mistral->embedding()->create([
    "A string here",
    "Another one here",
]);

```

### `Chat` Resource

#### Create Chat Completion

```php
$responseChat = $mistral->chat()->create(
    messages: [
        [
            "role" => "user",
            "content" => "Write hello world in BASH",
        ]
    ],
    model: Model::medium->value,
    temperature: 0.4,
    maxTokens: 100,
    safeMode: false
);
```

#### Create Streamed Chat Completions

```php

$chunks = $this->mistral->chat()->createStreamed(
    messages: [
        [
            'role' => 'user', 
            'content' => 'Make a markdown list of 10 common fruits'
        ],
    ],
    model: Model::tiny->value,
);

foreach ($chunks as $chunk) {
    echo $chunk->id; // 'cmpl-0339459d35cb441b9f111b94216cff97'
    echo $chunk->model; // 'mistral-tiny'
    echo $chunk->object; // 'chat.completion.chunk'
    echo $chunk->created; // DateTime

    foreach ($chunk->choices as $choice) {
        $choice->index; // 0
        $choice->delta->role; // 'assistant'
        $choice->delta->content; // 'Fruit list...'
        $choice->finishReason; // 'length'
    }
}

```



### `SimpleChat` Resource

For convenience, the client also provides a simple chat completion method, which returns a simpler, condensed and
flattened DTO, which is useful for quick prototyping.

#### Create simple chat completions

```php
$responseChat = $mistral->simpleChat()->create(
    messages: [
        [
            "role" => "user",
            "content" => "Hello world!",
        ],
    ],
    model: Model::medium->value,
    temperature: 0.4,
    maxTokens: 1500,
    safeMode: false
);
```

### `SimpleChat` Resource

For convenience, the client also provides a simple chat completion method, which returns a simpler, condensed, and
flattened DTO, useful for quick prototyping.

#### Create Streamed Simple Chat Completions

```php
use HelgeSverre\Mistral\Enums\Role;

$response = $this->mistral->simpleChat()->stream(
    messages: [
        [
            'role' => Role::user->value,
            'content' => 'Say the word "banana"',
        ],
    ],
    maxTokens: 100,
);

foreach ($response as $chunk) {
    $chunk->id;           // 'cmpl-716e95d336db4e51a04cbcf2b84d1a76'
    $chunk->model;        // 'mistral-medium'
    $chunk->object;       // 'chat.completion.chunk'
    $chunk->created;      // '2024-01-03 12:00:00'
    $chunk->role;         // 'assistant'
    $chunk->content;      // 'the text \n'
    $chunk->finishReason; // 'length'
}
```

## Models

The `Model` enum in the Package, is simply a convenient way to refer to a string corresponding to a model name in the
Mistral api, you're free to use the string value directly if you prefer.

| Enum Case              | String Value       | Documentation Link                                                                 |
|------------------------|--------------------|------------------------------------------------------------------------------------|
| `Model::medium->value` | `'mistral-medium'` | [Mistral Medium Docs](https://docs.mistral.ai/platform/endpoints/#medium)          |
| `Model::small->value`  | `'mistral-small'`  | [Mistral Small Docs](https://docs.mistral.ai/platform/endpoints/#small)            |
| `Model::tiny->value`   | `'mistral-tiny'`   | [Mistral Tiny Docs](https://docs.mistral.ai/platform/endpoints/#tiny)              |
| `Model::embed->value`  | `'mistral-embed'`  | [Mistral Embed Docs](https://docs.mistral.ai/platform/endpoints/#embedding-models) |

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
