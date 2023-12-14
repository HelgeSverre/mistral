<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Embedding\Embedding;
use HelgeSverre\Mistral\Dto\Embedding\EmbeddingResponse;
use HelgeSverre\Mistral\Requests\Embedding\CreateEmbedding;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Spatie\LaravelData\DataCollection;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
});
it('CreateEmbedding works', function () {
    Saloon::fake([
        CreateEmbedding::class => MockResponse::fixture('embedding.createEmbedding'),
    ]);

    $response = $this->mistral->embedding()->create(
        input: ['mistral-php testing']
    );

    Saloon::assertSent(CreateEmbedding::class);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBeArray()
        ->and($response->body())->json();

});

it('CreateEmbedding response can be cast to DTO', function () {
    Saloon::fake([
        CreateEmbedding::class => MockResponse::fixture('embedding.createEmbedding'),
    ]);

    /** @var EmbeddingResponse $dto */
    $dto = $this->mistral->embedding()->create(input: ['mistral-php testing'])->dto();

    expect($dto)->toBeInstanceOf(EmbeddingResponse::class)
        ->and($dto->id)->not()->toBeNull()
        ->and($dto->object)->not()->toBeNull()
        ->and($dto->data)->not()->toBeNull()
        ->and($dto->data)->toBeInstanceOf(DataCollection::class)
        ->and($dto->data[0])->toBeInstanceOf(Embedding::class)
        ->and($dto->model)->not()->toBeNull()
        ->and($dto->object)->toBe('list');

});
