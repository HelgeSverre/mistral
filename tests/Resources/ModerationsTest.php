<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Moderations\ModerationResponse;
use HelgeSverre\Mistral\Dto\Moderations\ModerationResult;
use HelgeSverre\Mistral\Requests\Moderations\CreateChatModerationRequest;
use HelgeSverre\Mistral\Requests\Moderations\CreateModerationRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Spatie\LaravelData\DataCollection;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
});

it('can moderate text with safe content', function () {
    Saloon::fake([
        CreateModerationRequest::class => MockResponse::fixture('moderations/text_safe'),
    ]);

    $response = $this->mistral->moderations()->moderate(
        model: 'mistral-moderation-latest',
        input: 'This is a safe message'
    );

    Saloon::assertSent(CreateModerationRequest::class);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBeArray()
        ->and($response->body())->json();
});

it('can moderate text and cast to DTO', function () {
    Saloon::fake([
        CreateModerationRequest::class => MockResponse::fixture('moderations/text_safe'),
    ]);

    /** @var ModerationResponse $dto */
    $dto = $this->mistral->moderations()->moderateAsDto(
        model: 'mistral-moderation-latest',
        input: 'This is a safe message'
    );

    expect($dto)->toBeInstanceOf(ModerationResponse::class)
        ->and($dto->id)->toBe('modr-abc123')
        ->and($dto->model)->toBe('mistral-moderation-latest')
        ->and($dto->results)->toBeInstanceOf(DataCollection::class)
        ->and($dto->results)->toHaveCount(1)
        ->and($dto->results[0])->toBeInstanceOf(ModerationResult::class)
        ->and($dto->results[0]->flagged)->toBeFalse()
        ->and($dto->results[0]->categories->sexual)->toBeFalse()
        ->and($dto->results[0]->categories->hate)->toBeFalse()
        ->and($dto->results[0]->categories->violence)->toBeFalse()
        ->and($dto->results[0]->categories->selfHarm)->toBeFalse()
        ->and($dto->results[0]->categories->sexualMinors)->toBeFalse()
        ->and($dto->results[0]->categories->hateThreatening)->toBeFalse()
        ->and($dto->results[0]->categories->violenceGraphic)->toBeFalse();
});

it('can detect flagged content', function () {
    Saloon::fake([
        CreateModerationRequest::class => MockResponse::fixture('moderations/text_flagged'),
    ]);

    /** @var ModerationResponse $dto */
    $dto = $this->mistral->moderations()->moderateAsDto(
        model: 'mistral-moderation-latest',
        input: 'This is a harmful message'
    );

    expect($dto)->toBeInstanceOf(ModerationResponse::class)
        ->and($dto->id)->toBe('modr-def456')
        ->and($dto->results[0]->flagged)->toBeTrue()
        ->and($dto->results[0]->categories->hate)->toBeTrue()
        ->and($dto->results[0]->categories->violence)->toBeTrue()
        ->and($dto->results[0]->categories->hateThreatening)->toBeTrue()
        ->and($dto->results[0]->categoryScores->hate)->toBeGreaterThan(0.8)
        ->and($dto->results[0]->categoryScores->violence)->toBeGreaterThan(0.7)
        ->and($dto->results[0]->categoryScores->hateThreatening)->toBeGreaterThan(0.8);
});

it('can moderate array of text inputs', function () {
    Saloon::fake([
        CreateModerationRequest::class => MockResponse::fixture('moderations/text_multiple'),
    ]);

    /** @var ModerationResponse $dto */
    $dto = $this->mistral->moderations()->moderateAsDto(
        model: 'mistral-moderation-latest',
        input: [
            'First safe message',
            'Second harmful message',
            'Third safe message',
        ]
    );

    expect($dto)->toBeInstanceOf(ModerationResponse::class)
        ->and($dto->id)->toBe('modr-ghi789')
        ->and($dto->results)->toHaveCount(3)
        ->and($dto->results[0]->flagged)->toBeFalse()
        ->and($dto->results[1]->flagged)->toBeTrue()
        ->and($dto->results[2]->flagged)->toBeFalse();
});

it('can check category scores', function () {
    Saloon::fake([
        CreateModerationRequest::class => MockResponse::fixture('moderations/text_safe'),
    ]);

    /** @var ModerationResponse $dto */
    $dto = $this->mistral->moderations()->moderateAsDto(
        model: 'mistral-moderation-latest',
        input: 'This is a safe message'
    );

    $scores = $dto->results[0]->categoryScores;

    expect($scores->sexual)->toBe(0.000123)
        ->and($scores->hate)->toBe(0.000045)
        ->and($scores->violence)->toBe(0.000078)
        ->and($scores->selfHarm)->toBe(0.000012)
        ->and($scores->sexualMinors)->toBe(0.000003)
        ->and($scores->hateThreatening)->toBe(0.000001)
        ->and($scores->violenceGraphic)->toBe(0.000034);
});

it('can moderate chat conversation', function () {
    Saloon::fake([
        CreateChatModerationRequest::class => MockResponse::fixture('moderations/chat'),
    ]);

    $response = $this->mistral->moderations()->moderateChat(
        model: 'mistral-moderation-latest',
        messages: [
            ['role' => 'user', 'content' => 'Hello'],
            ['role' => 'assistant', 'content' => 'Hi there!'],
            ['role' => 'user', 'content' => 'How are you?'],
        ]
    );

    Saloon::assertSent(CreateChatModerationRequest::class);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBeArray()
        ->and($response->body())->json();
});

it('can moderate chat and cast to DTO', function () {
    Saloon::fake([
        CreateChatModerationRequest::class => MockResponse::fixture('moderations/chat'),
    ]);

    /** @var ModerationResponse $dto */
    $dto = $this->mistral->moderations()->moderateChatAsDto(
        model: 'mistral-moderation-latest',
        messages: [
            ['role' => 'user', 'content' => 'Hello'],
            ['role' => 'assistant', 'content' => 'Hi there!'],
        ]
    );

    expect($dto)->toBeInstanceOf(ModerationResponse::class)
        ->and($dto->id)->toBe('modr-chat123')
        ->and($dto->model)->toBe('mistral-moderation-latest')
        ->and($dto->results)->toBeInstanceOf(DataCollection::class)
        ->and($dto->results[0]->flagged)->toBeFalse();
});

it('handles special category names correctly', function () {
    Saloon::fake([
        CreateModerationRequest::class => MockResponse::fixture('moderations/text_flagged'),
    ]);

    /** @var ModerationResponse $dto */
    $dto = $this->mistral->moderations()->moderateAsDto(
        model: 'mistral-moderation-latest',
        input: 'Test message'
    );

    // Verify MapName attributes work correctly for special category names
    $categories = $dto->results[0]->categories;
    $scores = $dto->results[0]->categoryScores;

    expect($categories->selfHarm)->toBeBool()
        ->and($categories->sexualMinors)->toBeBool()
        ->and($categories->hateThreatening)->toBeBool()
        ->and($categories->violenceGraphic)->toBeBool()
        ->and($scores->selfHarm)->toBeFloat()
        ->and($scores->sexualMinors)->toBeFloat()
        ->and($scores->hateThreatening)->toBeFloat()
        ->and($scores->violenceGraphic)->toBeFloat();
});
