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

    expect($response->model)->toBe(Model::mistral7b->value)
        ->and($response->role)->toBe('assistant')
        ->and($response->content)->toContain('banana')
        ->and($response->promptTokens)->toBeInt()
        ->and($response->completionTokens)->toBeInt()
        ->and($response->totalTokens)->toBeInt()
        ->and($response->object)->toBe('chat.completion');

});

it('create() works using json mode', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('simpleChat.createChatCompletion-jsonMode'),
    ]);

    $response = $this->mistral->simpleChat()->create(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Generate a single JSON object with the following fields: name, age, email: ',
            ],
        ],
        model: Model::small->value,
        maxTokens: 100,
        jsonMode: true
    );

    Saloon::assertSent(CreateChatCompletion::class);

    expect($response->model)->toBe(Model::small->value)
        ->and($response->role)->toBe('assistant')
        ->and($response->content)->toBeJson()
        ->and($response->contentAsJson())->toBe([
            'name' => 'John Doe',
            'age' => 30,
            'email' => 'johndoe@example.com',
        ])
        ->and($response->promptTokens)->toBeInt()
        ->and($response->completionTokens)->toBeInt()
        ->and($response->totalTokens)->toBeInt()
        ->and($response->object)->toBe('chat.completion');

});

it('create() with jsonMode throws on incompatible model', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('simpleChat.createChatCompletion-jsonMode-throw'),
    ]);

    $this->expectException(InvalidArgumentException::class);

    $response = $this->mistral->simpleChat()->create(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Generate a single JSON object with the following fields: name, age, email: ',
            ],
        ],
        model: Model::mistral7b->value,
        maxTokens: 100,
        jsonMode: true
    );
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

    expect($chunks)->not()->toBeEmpty(64)
        ->and($chunks[0]->model)->toBe(Model::mistral7b->value)
        ->and($chunks[0]->role)->toBe('assistant')
        ->and($chunks)->toContainOnlyInstancesOf(SimpleStreamChunk::class);

});

it('stream() works with json mode', function () {
    Saloon::fake([
        CreateChatCompletion::class => MockResponse::fixture('simpleChat.createStreamedChatCompletion-jsonMode'),
    ]);

    $response = $this->mistral->simpleChat()->stream(
        messages: [
            [
                'role' => Role::user->value,
                'content' => 'Generate a single JSON object with the following fields: name, age, email: ',
            ],
        ],
        model: Model::small->value,
        maxTokens: 100,
        jsonMode: true,
    );

    $chunks = iterator_to_array($response);

    Saloon::assertSent(CreateChatCompletion::class);

    expect($chunks)->not()->toBeEmpty(64)
        ->and($chunks[0]->model)->toBe(Model::small->value)
        ->and($chunks[0]->role)->toBe('assistant')
        ->and($chunks)->toContainOnlyInstancesOf(SimpleStreamChunk::class);

    $combined = implode('', array_map(fn (SimpleStreamChunk $chunk) => $chunk->content, $chunks));

    expect($combined)
        ->toBeJson()
        ->and(json_decode($combined, true))->toBe([
            'name' => 'John Doe',
            'age' => 30,
            'email' => 'john.doe@example.com',
        ]);

});
