<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Models\BaseModelCard;
use HelgeSverre\Mistral\Dto\Models\DeleteModelOut;
use HelgeSverre\Mistral\Dto\Models\FTModelCard;
use HelgeSverre\Mistral\Dto\Models\ModelList;
use HelgeSverre\Mistral\Requests\Models\DeleteModelRequest;
use HelgeSverre\Mistral\Requests\Models\ListModels;
use HelgeSverre\Mistral\Requests\Models\RetrieveModelRequest;
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

it('RetrieveModel works for base model', function () {
    Saloon::fake([
        RetrieveModelRequest::class => MockResponse::fixture('models.retrieveModel-base'),
    ]);

    $dto = $this->mistral->models()->retrieve('open-mistral-7b');

    Saloon::assertSent(RetrieveModelRequest::class);

    expect($dto)->toBeInstanceOf(BaseModelCard::class)
        ->and($dto->id)->toBe('open-mistral-7b')
        ->and($dto->object)->toBe('model')
        ->and($dto->type)->toBe('base')
        ->and($dto->ownedBy)->toBe('mistralai')
        ->and($dto->capabilities)->toBeObject()
        ->and($dto->capabilities->completionChat)->toBeTrue()
        ->and($dto->capabilities->functionCalling)->toBeTrue();
});

it('RetrieveModel works for fine-tuned model', function () {
    Saloon::fake([
        RetrieveModelRequest::class => MockResponse::fixture('models.retrieveModel-finetuned'),
    ]);

    $dto = $this->mistral->models()->retrieve('ft:open-mistral-7b:587a6b29:20240514:7e773925');

    Saloon::assertSent(RetrieveModelRequest::class);

    expect($dto)->toBeInstanceOf(FTModelCard::class)
        ->and($dto->id)->toBe('ft:open-mistral-7b:587a6b29:20240514:7e773925')
        ->and($dto->object)->toBe('model')
        ->and($dto->type)->toBe('fine-tuned')
        ->and($dto->job)->toBe('ftjob-abc123')
        ->and($dto->root)->toBe('open-mistral-7b')
        ->and($dto->archived)->toBeFalse()
        ->and($dto->capabilities)->toBeObject();
});

it('DeleteModel works', function () {
    Saloon::fake([
        DeleteModelRequest::class => MockResponse::fixture('models.deleteModel'),
    ]);

    $dto = $this->mistral->models()->delete('ft:open-mistral-7b:587a6b29:20240514:7e773925');

    Saloon::assertSent(DeleteModelRequest::class);

    expect($dto)->toBeInstanceOf(DeleteModelOut::class)
        ->and($dto->id)->toBe('ft:open-mistral-7b:587a6b29:20240514:7e773925')
        ->and($dto->object)->toBe('model')
        ->and($dto->deleted)->toBeTrue();
});
