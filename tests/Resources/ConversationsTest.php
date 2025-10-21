<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Conversations\AgentConversation;
use HelgeSverre\Mistral\Dto\Conversations\ConversationAppendRequest;
use HelgeSverre\Mistral\Dto\Conversations\ConversationEntry;
use HelgeSverre\Mistral\Dto\Conversations\ConversationHistory;
use HelgeSverre\Mistral\Dto\Conversations\ConversationList;
use HelgeSverre\Mistral\Dto\Conversations\ConversationMessages;
use HelgeSverre\Mistral\Dto\Conversations\ConversationRequest;
use HelgeSverre\Mistral\Dto\Conversations\ConversationResponse;
use HelgeSverre\Mistral\Dto\Conversations\ConversationRestartRequest;
use HelgeSverre\Mistral\Dto\Conversations\ModelConversation;
use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Requests\Conversations\AppendToConversationRequest;
use HelgeSverre\Mistral\Requests\Conversations\CreateConversationRequest;
use HelgeSverre\Mistral\Requests\Conversations\GetConversationHistoryRequest;
use HelgeSverre\Mistral\Requests\Conversations\GetConversationMessagesRequest;
use HelgeSverre\Mistral\Requests\Conversations\GetConversationRequest;
use HelgeSverre\Mistral\Requests\Conversations\ListConversationsRequest;
use HelgeSverre\Mistral\Requests\Conversations\RestartConversationRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Spatie\LaravelData\DataCollection;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
});

it('can create a conversation with a model', function () {
    Saloon::fake([
        CreateConversationRequest::class => MockResponse::fixture('conversations/create'),
    ]);

    $response = $this->mistral->conversations()->create(
        new ConversationRequest(
            model: Model::large->value,
            messages: [
                ['role' => 'user', 'content' => 'Hello, how are you?'],
            ]
        )
    );

    Saloon::assertSent(CreateConversationRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(ConversationResponse::class)
        ->and($dto->conversationId)->toBe('conv_abc123')
        ->and($dto->entries)->toBeInstanceOf(DataCollection::class)
        ->and($dto->entries)->toHaveCount(2)
        ->and($dto->entries[0])->toBeInstanceOf(ConversationEntry::class)
        ->and($dto->entries[0]->role)->toBe('user')
        ->and($dto->entries[0]->content)->toBe('Hello, how are you?')
        ->and($dto->entries[1]->role)->toBe('assistant')
        ->and($dto->entries[1]->content)->toContain('doing well');
});

it('can create a conversation with an agent', function () {
    Saloon::fake([
        CreateConversationRequest::class => MockResponse::fixture('conversations/create-with-agent'),
    ]);

    $response = $this->mistral->conversations()->create(
        new ConversationRequest(
            agentId: 'agent_123',
            messages: [
                ['role' => 'user', 'content' => 'Tell me about the weather'],
            ]
        )
    );

    Saloon::assertSent(CreateConversationRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(ConversationResponse::class)
        ->and($dto->conversationId)->toBe('conv_agent_xyz789')
        ->and($dto->entries)->toBeInstanceOf(DataCollection::class)
        ->and($dto->entries)->toHaveCount(2)
        ->and($dto->entries[1]->toolCalls)->toBeArray()
        ->and($dto->entries[1]->toolCalls[0]['function']['name'])->toBe('get_weather');
});

it('can list conversations', function () {
    Saloon::fake([
        ListConversationsRequest::class => MockResponse::fixture('conversations/list'),
    ]);

    $response = $this->mistral->conversations()->list();

    Saloon::assertSent(ListConversationsRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(ConversationList::class)
        ->and($dto->object)->toBe('list')
        ->and($dto->data)->toBeArray()
        ->and($dto->data)->toHaveCount(3)
        ->and($dto->hasMore)->toBe(false)
        ->and($dto->firstId)->toBe('conv_abc123')
        ->and($dto->lastId)->toBe('conv_agent_xyz789');
});

it('can list conversations with pagination', function () {
    Saloon::fake([
        ListConversationsRequest::class => MockResponse::fixture('conversations/list'),
    ]);

    $response = $this->mistral->conversations()->list(page: 1, pageSize: 10, order: 'desc');

    Saloon::assertSent(ListConversationsRequest::class);

    expect($response->status())->toBe(200);
});

it('can get a model-based conversation', function () {
    Saloon::fake([
        GetConversationRequest::class => MockResponse::fixture('conversations/get'),
    ]);

    $response = $this->mistral->conversations()->get('conv_abc123');

    Saloon::assertSent(GetConversationRequest::class);

    expect($response->status())->toBe(200);

    $dto = ModelConversation::from($response->json());

    expect($dto)->toBeInstanceOf(ModelConversation::class)
        ->and($dto->id)->toBe('conv_abc123')
        ->and($dto->object)->toBe('conversation')
        ->and($dto->model)->toBe('mistral-large-latest')
        ->and($dto->createdAt)->toBe(1728648000);
});

it('can get an agent-based conversation', function () {
    Saloon::fake([
        GetConversationRequest::class => MockResponse::fixture('conversations/get-agent'),
    ]);

    $response = $this->mistral->conversations()->get('conv_agent_xyz789');

    Saloon::assertSent(GetConversationRequest::class);

    expect($response->status())->toBe(200);

    $dto = AgentConversation::from($response->json());

    expect($dto)->toBeInstanceOf(AgentConversation::class)
        ->and($dto->id)->toBe('conv_agent_xyz789')
        ->and($dto->object)->toBe('conversation')
        ->and($dto->agentId)->toBe('agent_123')
        ->and($dto->createdAt)->toBe(1728648200);
});

it('can append messages to a conversation', function () {
    Saloon::fake([
        AppendToConversationRequest::class => MockResponse::fixture('conversations/append'),
    ]);

    $response = $this->mistral->conversations()->append(
        'conv_abc123',
        new ConversationAppendRequest(
            messages: [
                ['role' => 'user', 'content' => "What's 2 + 2?"],
            ]
        )
    );

    Saloon::assertSent(AppendToConversationRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(ConversationResponse::class)
        ->and($dto->conversationId)->toBe('conv_abc123')
        ->and($dto->entries)->toBeInstanceOf(DataCollection::class)
        ->and($dto->entries)->toHaveCount(2)
        ->and($dto->entries[0]->content)->toBe("What's 2 + 2?")
        ->and($dto->entries[1]->content)->toBe('2 + 2 equals 4.');
});

it('can get conversation history', function () {
    Saloon::fake([
        GetConversationHistoryRequest::class => MockResponse::fixture('conversations/history'),
    ]);

    $response = $this->mistral->conversations()->getHistory('conv_abc123');

    Saloon::assertSent(GetConversationHistoryRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(ConversationHistory::class)
        ->and($dto->entries)->toBeInstanceOf(DataCollection::class)
        ->and($dto->entries)->toHaveCount(4)
        ->and($dto->entries[0])->toBeInstanceOf(ConversationEntry::class)
        ->and($dto->entries[0]->role)->toBe('user')
        ->and($dto->entries[1]->role)->toBe('assistant');
});

it('can get conversation messages only', function () {
    Saloon::fake([
        GetConversationMessagesRequest::class => MockResponse::fixture('conversations/messages'),
    ]);

    $response = $this->mistral->conversations()->getMessages('conv_abc123');

    Saloon::assertSent(GetConversationMessagesRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(ConversationMessages::class)
        ->and($dto->messages)->toBeInstanceOf(DataCollection::class)
        ->and($dto->messages)->toHaveCount(4)
        ->and($dto->messages[0])->toBeInstanceOf(ConversationEntry::class);
});

it('can restart a conversation', function () {
    Saloon::fake([
        RestartConversationRequest::class => MockResponse::fixture('conversations/restart'),
    ]);

    $response = $this->mistral->conversations()->restart(
        'conv_abc123',
        new ConversationRestartRequest(
            entryId: 'entry_001'
        )
    );

    Saloon::assertSent(RestartConversationRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(ConversationResponse::class)
        ->and($dto->conversationId)->toBe('conv_abc123')
        ->and($dto->entries)->toBeInstanceOf(DataCollection::class)
        ->and($dto->entries)->toHaveCount(2)
        ->and($dto->entries[0]->content)->toBe("Let's start over");
});

it('can restart a conversation with new messages', function () {
    Saloon::fake([
        RestartConversationRequest::class => MockResponse::fixture('conversations/restart'),
    ]);

    $response = $this->mistral->conversations()->restart(
        'conv_abc123',
        new ConversationRestartRequest(
            entryId: 'entry_001',
            messages: [
                ['role' => 'user', 'content' => "Let's start over"],
            ],
            temperature: 0.8,
            maxTokens: 1000
        )
    );

    Saloon::assertSent(RestartConversationRequest::class);

    expect($response->status())->toBe(200);
});

it('works with all conversation parameters', function () {
    Saloon::fake([
        CreateConversationRequest::class => MockResponse::fixture('conversations/create'),
    ]);

    $response = $this->mistral->conversations()->create(
        new ConversationRequest(
            model: Model::large->value,
            messages: [
                ['role' => 'user', 'content' => 'Test message'],
            ],
            temperature: 0.7,
            maxTokens: 1500,
            minTokens: 100,
            topP: 0.9,
            randomSeed: 42,
            safePrompt: false,
            responseFormat: ['type' => 'json_object'],
            metadata: ['user_id' => '123'],
            stop: ['END', 'STOP'],
            presencePenalty: 0.6,
            frequencyPenalty: 0.3
        )
    );

    Saloon::assertSent(CreateConversationRequest::class);

    expect($response->status())->toBe(200);
});

it('works with tools in conversation', function () {
    Saloon::fake([
        CreateConversationRequest::class => MockResponse::fixture('conversations/create-with-agent'),
    ]);

    $response = $this->mistral->conversations()->create(
        new ConversationRequest(
            model: Model::large->value,
            messages: [
                ['role' => 'user', 'content' => 'What is the weather?'],
            ],
            tools: [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'get_weather',
                        'description' => 'Get the current weather',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'location' => [
                                    'type' => 'string',
                                    'description' => 'The location to get weather for',
                                ],
                            ],
                            'required' => ['location'],
                        ],
                    ],
                ],
            ],
            toolChoice: 'auto'
        )
    );

    Saloon::assertSent(CreateConversationRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();

    expect($dto->entries[1]->toolCalls)->toBeArray();
});
