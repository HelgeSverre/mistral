<p align="center"><img src="./art/header.png"></p>

# Laravel Client for Mistral.AI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/helgesverre/mistral.svg?style=flat-square)](https://packagist.org/packages/helgesverre/mistral)
[![Total Downloads](https://img.shields.io/packagist/dt/helgesverre/mistral.svg?style=flat-square)](https://packagist.org/packages/helgesverre/mistral)

The Mistral.ai Laravel Client enables laravel applications to interact with the Mistral.ai API, providing
straightforward access to features like chat completions and text embeddings.

Get your API key at [console.mistral.ai](https://console.mistral.ai/).

## Installation

You can install the package via composer:

```bash
composer require helgesverre/mistral
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="mistral-config"
```

This is the contents of the published config file:

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
use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Mistral;

// Instantiate the client
$mistral = new Mistral(apiKey: config('mistral.api_key'));

// Or use the Facade (Laravel)
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
$response = $mistral->models()->list();

/** @var \HelgeSverre\Mistral\Dto\Embedding\EmbeddingResponse $dto */
$dto = $response->dto();
```

### `Embeddings` Resource

#### Create embedding

```php
$response = $mistral->embedding()->create([
    "A string here",
    "Another one here",
]);


/** @var EmbeddingResponse $dto */
$dto = $response->dto();
```

### `Chat` Resource

#### Create Chat Completion

```php
$response = $mistral->chat()->create(
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

/** @var ChatCompletionResponse $dto */
$dto = $response->dto();

```

#### Create Streamed Chat Completions

```php
// Returns a generator, which you can iterate over to get the streamed chunks
$stream = $this->mistral->chat()->createStreamed(
    messages: [
        [
            'role' => 'user', 
            'content' => 'Make a markdown list of 10 common fruits'
        ],
    ],
    model: Model::tiny->value,
);

foreach ($stream as $chunk) {
    
    /** @var StreamedChatCompletionResponse $chunk */
    
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
$response = $mistral->simpleChat()->create(
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

/** @var ChatCompletionResponse $response */
```

### `SimpleChat` Resource

For convenience, the client also provides a simple chat completion method, which returns a simpler, condensed, and
flattened DTO, useful for quick prototyping.

#### Create Streamed Simple Chat Completions

```php
// Returns a generator, which you can iterate over to get the streamed chunks
$response = $this->mistral->simpleChat()->stream(
    messages: [
        [
            'role' => "user",
            'content' => 'Say the word "banana"',
        ],
    ],
    maxTokens: 100,
);

foreach ($response as $chunk) {
    /** @var SimpleStreamChunk $chunk */

    $chunk->id;           // 'cmpl-716e95d336db4e51a04cbcf2b84d1a76'
    $chunk->model;        // 'mistral-medium'
    $chunk->object;       // 'chat.completion.chunk'
    $chunk->created;      // '2024-01-03 12:00:00'
    $chunk->role;         // 'assistant'
    $chunk->content;      // 'the text \n'
    $chunk->finishReason; // 'length'
}
```

## List of DTOs

For convenience, here is a list of all the DTOs available in this package.

- Chat
    - [Chat/ChatCompletionChoice.php](./src/Dto/Chat/ChatCompletionChoice.php)
    - [Chat/ChatCompletionMessage.php](./src/Dto/Chat/ChatCompletionMessage.php)
    - [Chat/ChatCompletionRequest.php](./src/Dto/Chat/ChatCompletionRequest.php)
    - [Chat/ChatCompletionResponse.php](./src/Dto/Chat/ChatCompletionResponse.php)
    - [Chat/StreamedChatCompletionChoice.php](./src/Dto/Chat/StreamedChatCompletionChoice.php)
    - [Chat/StreamedChatCompletionDelta.php](./src/Dto/Chat/StreamedChatCompletionDelta.php)
    - [Chat/StreamedChatCompletionResponse.php](./src/Dto/Chat/StreamedChatCompletionResponse.php)
- Embedding
    - [Embedding/Embedding.php](./src/Dto/Embedding/Embedding.php)
    - [Embedding/EmbeddingRequest.php](./src/Dto/Embedding/EmbeddingRequest.php)
    - [Embedding/EmbeddingResponse.php](./src/Dto/Embedding/EmbeddingResponse.php)
- Models
    - [Models/Model.php](./src/Dto/Models/Model.php)
    - [Models/ModelList.php](./src/Dto/Models/ModelList.php)
    - [Models/ModelPermission.php](./src/Dto/Models/ModelPermission.php)
- SimpleChat
    - [SimpleChat/SimpleChatResponse.php](./src/Dto/SimpleChat/SimpleChatResponse.php)
    - [SimpleChat/SimpleStreamChunk.php](./src/Dto/SimpleChat/SimpleStreamChunk.php)
- Misc
    - [Usage.php](./src/Dto/Usage.php)

## List of available Mistral models

The following models are available in the Mistral API. You can use the `Model` enum in this package to refer to them, or
use the string value directly.

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
