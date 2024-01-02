<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Enums\Role;
use HelgeSverre\Mistral\Requests\Chat\CreateChatCompletion;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
});

it('create() works', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('simpleChat.createChatCompletion'),
    ]);

    $response = $this->mistral->simpleChat()->create(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Say the word "banana"',
            ],
        ],
        maxTokens: 100,
    );

    Saloon::assertSent(CreateChatCompletion::class);

    expect($response->model)->toBe(Model::tiny->value)
        ->and($response->role)->toBe('assistant')
        ->and($response->content)->toContain('banana')
        ->and($response->promptTokens)->toBe(15)
        ->and($response->completionTokens)->toBe(95)
        ->and($response->totalTokens)->toBe(110)
        ->and($response->id)->toBe('cmpl-29b1127043904c8e8bb806b9e912c57f')
        ->and($response->object)->toBe('chat.completion');

});

it('stream() works', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('simpleChat.createChatCompletion'),
    ]);

    $response = $this->mistral->simpleChat()->create(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Say the word "banana"',
            ],
        ],
        maxTokens: 100,
    );

    Saloon::assertSent(CreateChatCompletion::class);

    expect($response->model)->toBe(Model::tiny->value)
        ->and($response->role)->toBe('assistant')
        ->and($response->content)->toContain('banana')
        ->and($response->promptTokens)->toBe(15)
        ->and($response->completionTokens)->toBe(95)
        ->and($response->totalTokens)->toBe(110)
        ->and($response->id)->toBe('cmpl-29b1127043904c8e8bb806b9e912c57f')
        ->and($response->object)->toBe('chat.completion');

});
