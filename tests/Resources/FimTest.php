<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Fim\FIMChoice;
use HelgeSverre\Mistral\Dto\Fim\FIMCompletionResponse;
use HelgeSverre\Mistral\Dto\Usage;
use HelgeSverre\Mistral\Requests\Fim\CreateFIMCompletionRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Spatie\LaravelData\DataCollection;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
});

it('CreateFIMCompletion works with basic parameters', function () {
    Saloon::fake([
        CreateFIMCompletionRequest::class => MockResponse::fixture('fim/completion'),
    ]);

    $response = $this->mistral->fim()->create(
        model: 'codestral-latest',
        prompt: 'def fibonacci(',
        maxTokens: 100
    );

    Saloon::assertSent(CreateFIMCompletionRequest::class);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBeArray()
        ->and($response->body())->json()
        ->and($response->json('model'))->toBe('codestral-latest')
        ->and($response->json('object'))->toBe('fim.completion');
});

it('CreateFIMCompletion response can be cast to DTO', function () {
    Saloon::fake([
        CreateFIMCompletionRequest::class => MockResponse::fixture('fim/completion'),
    ]);

    $response = $this->mistral->fim()->create(
        model: 'codestral-latest',
        prompt: 'def fibonacci(',
        maxTokens: 100
    );

    /** @var FIMCompletionResponse $dto */
    $dto = $response->dto();

    Saloon::assertSent(CreateFIMCompletionRequest::class);

    expect($dto)->toBeInstanceOf(FIMCompletionResponse::class)
        ->and($dto->id)->not()->toBeNull()
        ->and($dto->usage)->toBeInstanceOf(Usage::class)
        ->and($dto->choices)->toBeInstanceOf(DataCollection::class)
        ->and($dto->choices[0])->toBeInstanceOf(FIMChoice::class)
        ->and($dto->choices[0]->message)->toBe('n: int, memo: dict = None')
        ->and($dto->model)->toBe('codestral-latest')
        ->and($dto->object)->toBe('fim.completion');
});

it('CreateFIMCompletion works with suffix parameter', function () {
    Saloon::fake([
        CreateFIMCompletionRequest::class => MockResponse::fixture('fim/completion-with-suffix'),
    ]);

    $response = $this->mistral->fim()->create(
        model: 'codestral-latest',
        prompt: 'function calculateSum(',
        suffix: ') { return sum; }',
        maxTokens: 100
    );

    Saloon::assertSent(CreateFIMCompletionRequest::class);

    /** @var FIMCompletionResponse $dto */
    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(FIMCompletionResponse::class)
        ->and($dto->id)->not()->toBeNull()
        ->and($dto->choices[0]->message)->toBe('a: number, b: number')
        ->and($response->status())->toBe(200);
});

it('CreateFIMCompletion works with temperature parameter', function () {
    Saloon::fake([
        CreateFIMCompletionRequest::class => MockResponse::fixture('fim/completion'),
    ]);

    $response = $this->mistral->fim()->create(
        model: 'codestral-latest',
        prompt: 'def calculate(',
        temperature: 0.5,
        maxTokens: 50
    );

    Saloon::assertSent(CreateFIMCompletionRequest::class);

    expect($response->status())->toBe(200);
});

it('CreateFIMCompletion works with topP parameter', function () {
    Saloon::fake([
        CreateFIMCompletionRequest::class => MockResponse::fixture('fim/completion'),
    ]);

    $response = $this->mistral->fim()->create(
        model: 'codestral-latest',
        prompt: 'class User {',
        topP: 0.9,
        maxTokens: 150
    );

    Saloon::assertSent(CreateFIMCompletionRequest::class);

    expect($response->status())->toBe(200);
});

it('CreateFIMCompletion works with minTokens parameter', function () {
    Saloon::fake([
        CreateFIMCompletionRequest::class => MockResponse::fixture('fim/completion'),
    ]);

    $response = $this->mistral->fim()->create(
        model: 'codestral-latest',
        prompt: 'function processData(',
        minTokens: 10,
        maxTokens: 100
    );

    Saloon::assertSent(CreateFIMCompletionRequest::class);

    expect($response->status())->toBe(200);
});

it('CreateFIMCompletion works with randomSeed parameter', function () {
    Saloon::fake([
        CreateFIMCompletionRequest::class => MockResponse::fixture('fim/completion'),
    ]);

    $response = $this->mistral->fim()->create(
        model: 'codestral-latest',
        prompt: 'def generate(',
        randomSeed: 42,
        maxTokens: 100
    );

    Saloon::assertSent(CreateFIMCompletionRequest::class);

    expect($response->status())->toBe(200);
});

it('CreateFIMCompletion works with stop parameter as string', function () {
    Saloon::fake([
        CreateFIMCompletionRequest::class => MockResponse::fixture('fim/completion'),
    ]);

    $response = $this->mistral->fim()->create(
        model: 'codestral-latest',
        prompt: 'for i in range(',
        stop: '\n',
        maxTokens: 50
    );

    Saloon::assertSent(CreateFIMCompletionRequest::class);

    expect($response->status())->toBe(200);
});

it('CreateFIMCompletion works with stop parameter as array', function () {
    Saloon::fake([
        CreateFIMCompletionRequest::class => MockResponse::fixture('fim/completion'),
    ]);

    $response = $this->mistral->fim()->create(
        model: 'codestral-latest',
        prompt: 'async function fetchData(',
        stop: ['\n', ';'],
        maxTokens: 100
    );

    Saloon::assertSent(CreateFIMCompletionRequest::class);

    expect($response->status())->toBe(200);
});

it('CreateFIMCompletion works with all parameters combined', function () {
    Saloon::fake([
        CreateFIMCompletionRequest::class => MockResponse::fixture('fim/completion-with-suffix'),
    ]);

    $response = $this->mistral->fim()->create(
        model: 'codestral-latest',
        prompt: 'def fibonacci(',
        suffix: '):\n    return result',
        temperature: 0.3,
        topP: 0.95,
        maxTokens: 200,
        minTokens: 5,
        randomSeed: 123,
        stop: ['\n\n', 'def']
    );

    Saloon::assertSent(CreateFIMCompletionRequest::class);

    /** @var FIMCompletionResponse $dto */
    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(FIMCompletionResponse::class)
        ->and($response->status())->toBe(200)
        ->and($dto->model)->toBe('codestral-latest');
});

it('CreateFIMCompletion works with code autocompletion scenario', function () {
    Saloon::fake([
        CreateFIMCompletionRequest::class => MockResponse::fixture('fim/completion'),
    ]);

    $response = $this->mistral->fim()->create(
        model: 'codestral-latest',
        prompt: '<?php\n\nclass UserController {\n    public function index(',
        suffix: ') {\n        // Method implementation\n    }\n}',
        temperature: 0.2,
        maxTokens: 50
    );

    Saloon::assertSent(CreateFIMCompletionRequest::class);

    expect($response->status())->toBe(200)
        ->and($response->json('choices'))->toBeArray()
        ->and($response->json('choices.0.message'))->toBeString();
});
