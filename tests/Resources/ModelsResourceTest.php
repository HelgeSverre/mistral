<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Models\ModelList;
use HelgeSverre\Mistral\Requests\Models\ListModels;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Spatie\LaravelData\DataCollection;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
});

it('ListModels works', function () {
    Saloon::fake([
        ListModels::class => MockResponse::fixture('models.listModels'),
    ]);

    $response = $this->mistral->models()->list();

    Saloon::assertSent(ListModels::class);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBeArray()
        ->and($response->body())->json();

});

it('ListModels response can be cast to DTO', function () {
    Saloon::fake([
        ListModels::class => MockResponse::fixture('models.listModels'),
    ]);

    /** @var ModelList $dto */
    $dto = $this->mistral->models()->list()->dto();

    expect($dto)->toBeInstanceOf(ModelList::class)
        ->and($dto->object)->toBe('list')
        ->and($dto->data)->toBeInstanceOf(DataCollection::class)
        ->and($dto->data[0]->object)->toBe('model');

});
