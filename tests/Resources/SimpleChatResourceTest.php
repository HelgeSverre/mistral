<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\SimpleChat\SimpleStreamChunk;
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
        ->and($response->completionTokens)->toBe(49)
        ->and($response->totalTokens)->toBe(64)
        ->and($response->id)->toBe('74d827432b10407bbf3e168cac335681')
        ->and($response->object)->toBe('chat.completion');

});

it('stream() works', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('simpleChat.createStreamedChatCompletion'),
    ]);

    $response = $this->mistral->simpleChat()->stream(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Say the word "banana"',
            ],
        ],
        maxTokens: 100,
    );

    $chunks = iterator_to_array($response);

    Saloon::assertSent(CreateChatCompletion::class);

    expect($chunks)->toHaveCount(13)
        ->and($chunks[0]->model)->toBe(Model::tiny->value)
        ->and($chunks[0]->role)->toBe('assistant')
        ->and($chunks)->toContainOnlyInstancesOf(SimpleStreamChunk::class);

});
