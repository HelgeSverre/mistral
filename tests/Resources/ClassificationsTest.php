<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Classifications\ClassificationResponse;
use HelgeSverre\Mistral\Dto\Classifications\ClassificationResult;
use HelgeSverre\Mistral\Requests\Classifications\CreateChatClassificationRequest;
use HelgeSverre\Mistral\Requests\Classifications\CreateClassificationRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Spatie\LaravelData\DataCollection;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
});

it('can classify single text input', function () {
    Saloon::fake([
        CreateClassificationRequest::class => MockResponse::fixture('classifications/text'),
    ]);

    $response = $this->mistral->classifications()->classify(
        model: 'mistral-classifier-latest',
        input: 'This is a technical article about machine learning'
    );

    Saloon::assertSent(CreateClassificationRequest::class);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBeArray()
        ->and($response->body())->json();
});

it('can classify single text input and return DTO', function () {
    Saloon::fake([
        CreateClassificationRequest::class => MockResponse::fixture('classifications/text'),
    ]);

    $dto = $this->mistral->classifications()->classifyAsDto(
        model: 'mistral-classifier-latest',
        input: 'This is a technical article about machine learning'
    );

    expect($dto)->toBeInstanceOf(ClassificationResponse::class)
        ->and($dto->id)->toBe('class_01234567')
        ->and($dto->model)->toBe('mistral-classifier-latest')
        ->and($dto->results)->toBeInstanceOf(DataCollection::class)
        ->and($dto->results)->toHaveCount(1)
        ->and($dto->results[0])->toBeInstanceOf(ClassificationResult::class)
        ->and($dto->results[0]->predictedClass)->toBe('technology')
        ->and($dto->results[0]->categories)->toBeArray()
        ->and($dto->results[0]->categories['technology'])->toBe(0.95)
        ->and($dto->results[0]->categoryScores)->toBeArray()
        ->and($dto->results[0]->categoryScores['technology'])->toBe(4.75);
});

it('can classify multiple text inputs', function () {
    Saloon::fake([
        CreateClassificationRequest::class => MockResponse::fixture('classifications/text_array'),
    ]);

    $response = $this->mistral->classifications()->classify(
        model: 'mistral-classifier-latest',
        input: [
            'First article about sports',
            'Second article about politics',
            'Third article about technology',
        ]
    );

    Saloon::assertSent(CreateClassificationRequest::class);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBeArray();
});

it('can classify multiple text inputs and return DTO', function () {
    Saloon::fake([
        CreateClassificationRequest::class => MockResponse::fixture('classifications/text_array'),
    ]);

    $dto = $this->mistral->classifications()->classifyAsDto(
        model: 'mistral-classifier-latest',
        input: [
            'First article about sports',
            'Second article about politics',
            'Third article about technology',
        ]
    );

    expect($dto)->toBeInstanceOf(ClassificationResponse::class)
        ->and($dto->results)->toHaveCount(3)
        ->and($dto->results[0]->predictedClass)->toBe('sports')
        ->and($dto->results[1]->predictedClass)->toBe('politics')
        ->and($dto->results[2]->predictedClass)->toBe('technology')
        ->and($dto->results[0]->categories['sports'])->toBe(0.92)
        ->and($dto->results[1]->categories['politics'])->toBe(0.88)
        ->and($dto->results[2]->categories['technology'])->toBe(0.94);
});

it('can classify chat conversation', function () {
    Saloon::fake([
        CreateChatClassificationRequest::class => MockResponse::fixture('classifications/chat'),
    ]);

    $response = $this->mistral->classifications()->classifyChat(
        model: 'mistral-classifier-latest',
        messages: [
            ['role' => 'user', 'content' => 'My internet is not working'],
            ['role' => 'assistant', 'content' => 'I can help you troubleshoot that issue'],
        ]
    );

    Saloon::assertSent(CreateChatClassificationRequest::class);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBeArray();
});

it('can classify chat conversation and return DTO', function () {
    Saloon::fake([
        CreateChatClassificationRequest::class => MockResponse::fixture('classifications/chat'),
    ]);

    $dto = $this->mistral->classifications()->classifyChatAsDto(
        model: 'mistral-classifier-latest',
        messages: [
            ['role' => 'user', 'content' => 'My internet is not working'],
            ['role' => 'assistant', 'content' => 'I can help you troubleshoot that issue'],
        ]
    );

    expect($dto)->toBeInstanceOf(ClassificationResponse::class)
        ->and($dto->id)->toBe('class_01234569')
        ->and($dto->model)->toBe('mistral-classifier-latest')
        ->and($dto->results)->toHaveCount(1)
        ->and($dto->results[0]->predictedClass)->toBe('technical_support')
        ->and($dto->results[0]->categories['technical_support'])->toBe(0.89)
        ->and($dto->results[0]->categoryScores['technical_support'])->toBe(3.85);
});

it('correctly extracts predicted class from results', function () {
    Saloon::fake([
        CreateClassificationRequest::class => MockResponse::fixture('classifications/text'),
    ]);

    $dto = $this->mistral->classifications()->classifyAsDto(
        model: 'mistral-classifier-latest',
        input: 'Test text'
    );

    $result = $dto->results[0];

    expect($result->predictedClass)->toBe('technology')
        ->and(array_keys($result->categories))->toContain('technology', 'science', 'business');
});

it('correctly handles dynamic categories', function () {
    Saloon::fake([
        CreateClassificationRequest::class => MockResponse::fixture('classifications/text_array'),
    ]);

    $dto = $this->mistral->classifications()->classifyAsDto(
        model: 'mistral-classifier-latest',
        input: ['Text 1', 'Text 2', 'Text 3']
    );

    expect($dto->results[0]->categories)->toHaveKeys(['sports', 'health', 'entertainment'])
        ->and($dto->results[1]->categories)->toHaveKeys(['politics', 'news', 'opinion'])
        ->and($dto->results[2]->categories)->toHaveKeys(['technology', 'science', 'business']);
});
