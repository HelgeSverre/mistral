<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Chat\ChatCompletionChoice;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionMessage;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionResponse;
use HelgeSverre\Mistral\Dto\Chat\ToolCalls;
use HelgeSverre\Mistral\Dto\Usage;
use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Enums\Role;
use HelgeSverre\Mistral\Requests\Chat\CreateChatCompletion;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Spatie\LaravelData\DataCollection;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
});

it('CreateChatCompletion works', function ($model) {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture("chat.createChatCompletion-with-{$model}-model"),
    ]);

    $response = $this->mistral->chat()->create(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Say the word "banana"',
            ],
        ],
        model: $model,
        maxTokens: 20,
    );

    Saloon::assertSent(CreateChatCompletion::class);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBeArray()
        ->and($response->body())->json()
        ->and($response->json('model'))->toBe($model);

})
    ->with([
        'open-mistral-7b',
        'open-mixtral-8x7b',
        'mistral-small-latest',
        'mistral-medium-latest',
        'mistral-large-latest',
    ]);

it('CreateChatCompletion works with json mode', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('chat.createChatCompletion-jsonMode'),
    ]);

    $response = $this->mistral->chat()->create(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Generate a single JSON object with the following fields: name, age, email: ',
            ],
        ],
        model: Model::small->value,
        maxTokens: 100,
        responseFormat: ['type' => 'json_object']
    );

    Saloon::assertSent(CreateChatCompletion::class);

    /** @var ChatCompletionResponse $dto */
    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(ChatCompletionResponse::class)
        ->and($dto->id)->not()->toBeNull()
        ->and($dto->usage)->toBeInstanceOf(Usage::class)
        ->and($dto->choices)->toBeInstanceOf(DataCollection::class)
        ->and($dto->choices[0])->toBeInstanceOf(ChatCompletionChoice::class)
        ->and($dto->choices[0]->message)->toBeInstanceOf(ChatCompletionMessage::class)
        ->and($dto->choices[0]->message->content)->toBe('{"name": "John Doe", "age": 30, "email": "johndoe@example.com"}')
        ->and($dto->model)->toBe(Model::small->value)
        ->and($dto->object)->toBe('chat.completion');
});

it('CreateChatCompletion works with function calling', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('chat.createChatCompletion-functionCall'),
    ]);

    $response = $this->mistral->chat()->create(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'What is the weather in Bergen, Norway?',
            ],
        ],
        model: Model::large->value,
        maxTokens: 1000,
        tools: [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'searchWeather',
                    'description' => 'Get the weather for a location',
                    'parameters' => [
                        'type' => 'object',
                        'required' => [
                            'location',
                        ],
                        'properties' => [
                            'location' => [
                                'type' => 'string',
                                'description' => 'The location to get the weather for.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'sendWeatherNotification',
                    'description' => 'Send notification about weather to a user',
                    'parameters' => [
                        'type' => 'object',
                        'required' => [
                            'userId',
                            'message',
                        ],
                        'properties' => [
                            'userId' => [
                                'type' => 'string',
                                'description' => 'the id of the user',
                            ],
                            'message' => [
                                'type' => 'string',
                                'description' => 'the message to send the user',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        toolChoice: 'any',
    );

    Saloon::assertSent(CreateChatCompletion::class);

    expect($response->json('choices.0.message.content'))->toBe('')
        ->and($response->json('choices.0.message.tool_calls'))->toBeArray()
        ->and($response->json('choices.0.message.tool_calls.0.id'))->toBe('null')
        ->and($response->json('choices.0.message.tool_calls.0.type'))->toBe('function')
        ->and($response->json('choices.0.message.tool_calls.0.function'))->toBeArray()
        ->and($response->json('choices.0.message.tool_calls.0.function.name'))->toBe('searchWeather')
        ->and($response->json('choices.0.message.tool_calls.0.function.arguments'))->toBeJson();

    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(ChatCompletionResponse::class)
        ->and($dto->id)->not()->toBeNull()
        ->and($dto->usage)->toBeInstanceOf(Usage::class)
        ->and($dto->choices)->toBeInstanceOf(DataCollection::class)
        ->and($dto->choices[0])->toBeInstanceOf(ChatCompletionChoice::class)
        ->and($dto->choices[0]->message)->toBeInstanceOf(ChatCompletionMessage::class)
        ->and($dto->choices[0]->message->toolCalls[0])->toBeInstanceOf(ToolCalls::class);

});

it('CreateChatCompletion response can be cast to DTO', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('chat.createChatCompletion'),
    ]);

    $response = $this->mistral->chat()->create(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Say the word "banana"',
            ],
        ]
    );

    /** @var ChatCompletionResponse $dto */
    $dto = $response->dto();

    Saloon::assertSent(CreateChatCompletion::class);

    expect($dto)->toBeInstanceOf(ChatCompletionResponse::class)
        ->and($dto->id)->not()->toBeNull()
        ->and($dto->usage)->toBeInstanceOf(Usage::class)
        ->and($dto->choices)->toBeInstanceOf(DataCollection::class)
        ->and($dto->choices[0])->toBeInstanceOf(ChatCompletionChoice::class)
        ->and($dto->choices[0]->message)->toBeInstanceOf(ChatCompletionMessage::class)
        ->and($dto->model)->toBe(Model::mistral7b->value)
        ->and($dto->object)->toBe('chat.completion');
});

it('CreateChatCompletion works with stop parameter as string', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('chat.createChatCompletion'),
    ]);

    $response = $this->mistral->chat()->create(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Count from 1 to 10',
            ],
        ],
        model: Model::small->value,
        stop: '5'
    );

    Saloon::assertSent(CreateChatCompletion::class);

    expect($response->status())->toBe(200);
});

it('CreateChatCompletion works with stop parameter as array', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('chat.createChatCompletion'),
    ]);

    $response = $this->mistral->chat()->create(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Write a story',
            ],
        ],
        model: Model::small->value,
        stop: ['The end', 'END']
    );

    Saloon::assertSent(CreateChatCompletion::class);

    expect($response->status())->toBe(200);
});

it('CreateChatCompletion works with presence_penalty', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('chat.createChatCompletion'),
    ]);

    $response = $this->mistral->chat()->create(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Generate diverse content',
            ],
        ],
        model: Model::small->value,
        presencePenalty: 0.6
    );

    Saloon::assertSent(CreateChatCompletion::class);

    expect($response->status())->toBe(200);
});

it('CreateChatCompletion works with frequency_penalty', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('chat.createChatCompletion'),
    ]);

    $response = $this->mistral->chat()->create(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Avoid repetition',
            ],
        ],
        model: Model::small->value,
        frequencyPenalty: 0.5
    );

    Saloon::assertSent(CreateChatCompletion::class);

    expect($response->status())->toBe(200);
});

it('CreateChatCompletion works with prediction parameter', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('chat.createChatCompletion'),
    ]);

    $response = $this->mistral->chat()->create(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Use cache',
            ],
        ],
        model: Model::small->value,
        prediction: [
            'type' => 'cache',
            'data' => ['key' => 'value'],
        ]
    );

    Saloon::assertSent(CreateChatCompletion::class);

    expect($response->status())->toBe(200);
});

it('CreateChatCompletion works with all new parameters combined', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('chat.createChatCompletion'),
    ]);

    $response = $this->mistral->chat()->create(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Generate creative text',
            ],
        ],
        model: Model::small->value,
        stop: ['END', 'STOP'],
        presencePenalty: 0.6,
        frequencyPenalty: 0.4,
        prediction: ['type' => 'cache']
    );

    Saloon::assertSent(CreateChatCompletion::class);

    /** @var ChatCompletionResponse $dto */
    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(ChatCompletionResponse::class)
        ->and($response->status())->toBe(200);
});
