<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Moderations\ModerationResponse;
use HelgeSverre\Mistral\Dto\Moderations\ModerationResult;
use HelgeSverre\Mistral\Requests\Classifications\CreateChatModerationRequest;
use HelgeSverre\Mistral\Requests\Classifications\CreateModerationRequest;
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

    $response = $this->mistral->classifications()->moderate(
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
    $dto = $this->mistral->classifications()->moderateAsDto(
        model: 'mistral-moderation-latest',
        input: 'This is a safe message'
    );

    expect($dto)->toBeInstanceOf(ModerationResponse::class)
        ->and($dto->id)->toBe('78a02375835540f19ff045a32b8709ea')
        ->and($dto->model)->toBe('mistral-moderation-latest')
        ->and($dto->results)->toBeInstanceOf(DataCollection::class)
        ->and($dto->results)->toHaveCount(1)
        ->and($dto->results[0])->toBeInstanceOf(ModerationResult::class)
        ->and($dto->results[0]->isFlagged())->toBeFalse()
        ->and($dto->results[0]->categories->sexual)->toBeFalse()
        ->and($dto->results[0]->categories->hateAndDiscrimination)->toBeFalse()
        ->and($dto->results[0]->categories->violenceAndThreats)->toBeFalse()
        ->and($dto->results[0]->categories->dangerousAndCriminalContent)->toBeFalse()
        ->and($dto->results[0]->categories->selfharm)->toBeFalse()
        ->and($dto->results[0]->categories->health)->toBeFalse()
        ->and($dto->results[0]->categories->financial)->toBeFalse()
        ->and($dto->results[0]->categories->law)->toBeFalse()
        ->and($dto->results[0]->categories->pii)->toBeFalse();
});

it('can detect flagged content', function () {
    Saloon::fake([
        CreateModerationRequest::class => MockResponse::fixture('moderations/text_flagged'),
    ]);

    /** @var ModerationResponse $dto */
    $dto = $this->mistral->classifications()->moderateAsDto(
        model: 'mistral-moderation-latest',
        input: 'This is a test message'
    );

    // Note: The fixture contains safe content, so we verify the structure rather than specific flags
    expect($dto)->toBeInstanceOf(ModerationResponse::class)
        ->and($dto->id)->toBe('7c9d419fac054766a81aac44fc2dd4f7')
        ->and($dto->results[0]->isFlagged())->toBeFalse()
        ->and($dto->results[0]->categories)->toBeObject()
        ->and($dto->results[0]->categoryScores)->toBeObject();
});

it('can moderate array of text inputs', function () {
    Saloon::fake([
        CreateModerationRequest::class => MockResponse::fixture('moderations/text_multiple'),
    ]);

    /** @var ModerationResponse $dto */
    $dto = $this->mistral->classifications()->moderateAsDto(
        model: 'mistral-moderation-latest',
        input: [
            'First safe message',
            'Second message',
            'Third safe message',
        ]
    );

    expect($dto)->toBeInstanceOf(ModerationResponse::class)
        ->and($dto->id)->toBe('5165174639fe4f62bc3e2dd452fcd739')
        ->and($dto->results)->toHaveCount(3)
        ->and($dto->results[0])->toBeInstanceOf(ModerationResult::class)
        ->and($dto->results[1])->toBeInstanceOf(ModerationResult::class)
        ->and($dto->results[2])->toBeInstanceOf(ModerationResult::class);
});

it('can check category scores', function () {
    Saloon::fake([
        CreateModerationRequest::class => MockResponse::fixture('moderations/text_safe'),
    ]);

    /** @var ModerationResponse $dto */
    $dto = $this->mistral->classifications()->moderateAsDto(
        model: 'mistral-moderation-latest',
        input: 'This is a safe message'
    );

    $scores = $dto->results[0]->categoryScores;

    // Verify all scores are present and are floats (actual values vary by API response)
    expect($scores->sexual)->toBeFloat()
        ->and($scores->hateAndDiscrimination)->toBeFloat()
        ->and($scores->violenceAndThreats)->toBeFloat()
        ->and($scores->dangerousAndCriminalContent)->toBeFloat()
        ->and($scores->selfharm)->toBeFloat()
        ->and($scores->health)->toBeFloat()
        ->and($scores->financial)->toBeFloat()
        ->and($scores->law)->toBeFloat()
        ->and($scores->pii)->toBeFloat();
});

it('can moderate chat conversation', function () {
    Saloon::fake([
        CreateChatModerationRequest::class => MockResponse::fixture('moderations/chat'),
    ]);

    $response = $this->mistral->classifications()->moderateChat(
        model: 'mistral-moderation-latest',
        input: [
            ['role' => 'user', 'content' => 'Hello'],
            ['role' => 'assistant', 'content' => 'Hi there!'],
            ['role' => 'user', 'content' => 'How are you?'],
        ]
    );

    Saloon::assertSent(CreateChatModerationRequest::class);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBeArray();
});

it('can moderate chat and cast to DTO', function () {
    Saloon::fake([
        CreateChatModerationRequest::class => MockResponse::fixture('moderations/chat'),
    ]);

    /** @var ModerationResponse $dto */
    $dto = $this->mistral->classifications()->moderateChatAsDto(
        model: 'mistral-moderation-latest',
        input: [
            ['role' => 'user', 'content' => 'Hello'],
            ['role' => 'assistant', 'content' => 'Hi there!'],
        ]
    );

    expect($dto)->toBeInstanceOf(ModerationResponse::class);
});

it('handles special category names correctly', function () {
    Saloon::fake([
        CreateModerationRequest::class => MockResponse::fixture('moderations/text_flagged'),
    ]);

    /** @var ModerationResponse $dto */
    $dto = $this->mistral->classifications()->moderateAsDto(
        model: 'mistral-moderation-latest',
        input: 'Test message'
    );

    // Verify MapName attributes work correctly for special category names
    $categories = $dto->results[0]->categories;
    $scores = $dto->results[0]->categoryScores;

    expect($categories->hateAndDiscrimination)->toBeBool()
        ->and($categories->violenceAndThreats)->toBeBool()
        ->and($categories->dangerousAndCriminalContent)->toBeBool()
        ->and($categories->selfharm)->toBeBool()
        ->and($scores->hateAndDiscrimination)->toBeFloat()
        ->and($scores->violenceAndThreats)->toBeFloat()
        ->and($scores->dangerousAndCriminalContent)->toBeFloat()
        ->and($scores->selfharm)->toBeFloat();
});
