<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Chat\ChatCompletionChoice;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionMessage;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionResponse;
use HelgeSverre\Mistral\Dto\Usage;
use HelgeSverre\Mistral\Enums\Role;
use HelgeSverre\Mistral\Requests\Chat\CreateChatCompletion;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Spatie\LaravelData\DataCollection;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
});

it('CreateChatCompletion works', function () {
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

    Saloon::assertSent(CreateChatCompletion::class);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBeArray()
        ->and($response->body())->json();

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
        ->and($dto->model)->toBe('mistral-tiny')
        ->and($dto->object)->toBe('chat.completion');
});
