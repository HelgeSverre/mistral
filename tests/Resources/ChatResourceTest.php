<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Chat\ChatCompletionChoice;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionMessage;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionResponse;
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
                'content' => "What's the status of the payment with id: FAKE_1234?",
            ],
        ],
        model: Model::large->value,
        maxTokens: 1000,
        tools: [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'retrievePaymentStatus',
                    'description' => 'Get payment status of a transaction id',
                    'parameters' => [
                        'type' => 'object',
                        'required' => [
                            'transactionId',
                        ],
                        'properties' => [
                            'transactionId' => [
                                'type' => 'string',
                                'description' => 'The transaction id.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'retrievePaymentDate',
                    'description' => 'Get payment date of a transaction id',
                    'parameters' => [
                        'type' => 'object',
                        'required' => [
                            'transactionId',
                        ],
                        'properties' => [
                            'transactionId' => [
                                'type' => 'string',
                                'description' => 'The transaction id.',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        toolChoice: 'any',
        responseFormat: ['type' => 'json_object']
    );

    Saloon::assertSent(CreateChatCompletion::class);

    // TODO: finish test once large model works

})->skip('The large model is not stable yet.');

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
