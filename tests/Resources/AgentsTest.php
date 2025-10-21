<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Agents\Agent;
use HelgeSverre\Mistral\Dto\Agents\AgentCreationRequest;
use HelgeSverre\Mistral\Dto\Agents\AgentList;
use HelgeSverre\Mistral\Dto\Agents\AgentUpdateRequest;
use HelgeSverre\Mistral\Requests\Agents\CreateAgentRequest;
use HelgeSverre\Mistral\Requests\Agents\GetAgentRequest;
use HelgeSverre\Mistral\Requests\Agents\ListAgentsRequest;
use HelgeSverre\Mistral\Requests\Agents\UpdateAgentRequest;
use HelgeSverre\Mistral\Requests\Agents\UpdateAgentVersionRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Spatie\LaravelData\DataCollection;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
});

it('can create an agent', function () {
    Saloon::fake([
        CreateAgentRequest::class => MockResponse::fixture('agents/create'),
    ]);

    $response = $this->mistral->agents()->create(
        new AgentCreationRequest(
            name: 'Customer Support Agent',
            model: 'mistral-large-latest',
            instructions: 'You are a helpful customer support assistant. Always be polite and professional.',
            description: 'Handles customer inquiries and support tickets',
            tools: [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'search_knowledge_base',
                        'description' => 'Search the knowledge base for relevant articles',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'query' => [
                                    'type' => 'string',
                                    'description' => 'The search query',
                                ],
                            ],
                            'required' => ['query'],
                        ],
                    ],
                ],
            ],
            temperature: 0.7,
            topP: 0.9
        )
    );

    Saloon::assertSent(CreateAgentRequest::class);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBeArray()
        ->and($response->body())->json();

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(Agent::class)
        ->and($dto->id)->toBe('ag:123456:20241011:a1b2c3d4')
        ->and($dto->object)->toBe('agent')
        ->and($dto->name)->toBe('Customer Support Agent')
        ->and($dto->model)->toBe('mistral-large-latest')
        ->and($dto->instructions)->toContain('customer support assistant')
        ->and($dto->description)->toBe('Handles customer inquiries and support tickets')
        ->and($dto->tools)->toBeArray()
        ->and($dto->tools)->toHaveCount(1)
        ->and($dto->temperature)->toBe(0.7)
        ->and($dto->topP)->toBe(0.9)
        ->and($dto->version)->toBe(1)
        ->and($dto->createdAt)->toBe(1728648000);
});

it('can create an agent with minimal parameters', function () {
    Saloon::fake([
        CreateAgentRequest::class => MockResponse::fixture('agents/create'),
    ]);

    $response = $this->mistral->agents()->create(
        new AgentCreationRequest(
            name: 'Simple Agent',
            model: 'mistral-large-latest'
        )
    );

    Saloon::assertSent(CreateAgentRequest::class);
    expect($response->status())->toBe(200);
});

it('can list agents', function () {
    Saloon::fake([
        ListAgentsRequest::class => MockResponse::fixture('agents/list'),
    ]);

    $response = $this->mistral->agents()->list();

    Saloon::assertSent(ListAgentsRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(AgentList::class)
        ->and($dto->object)->toBe('list')
        ->and($dto->data)->toBeInstanceOf(DataCollection::class)
        ->and($dto->data)->toHaveCount(2)
        ->and($dto->total)->toBe(2)
        ->and($dto->data[0])->toBeInstanceOf(Agent::class)
        ->and($dto->data[0]->id)->toBe('ag:123456:20241011:a1b2c3d4')
        ->and($dto->data[0]->name)->toBe('Customer Support Agent')
        ->and($dto->data[1]->id)->toBe('ag:123456:20241010:e5f6g7h8')
        ->and($dto->data[1]->name)->toBe('Sales Assistant')
        ->and($dto->data[1]->version)->toBe(2);
});

it('can list agents with pagination', function () {
    Saloon::fake([
        ListAgentsRequest::class => MockResponse::fixture('agents/list'),
    ]);

    $response = $this->mistral->agents()->list(page: 0, pageSize: 10);

    Saloon::assertSent(ListAgentsRequest::class);
    expect($response->status())->toBe(200);
});

it('can get a specific agent', function () {
    Saloon::fake([
        GetAgentRequest::class => MockResponse::fixture('agents/get'),
    ]);

    $agentId = 'ag:123456:20241011:a1b2c3d4';
    $response = $this->mistral->agents()->get($agentId);

    Saloon::assertSent(GetAgentRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(Agent::class)
        ->and($dto->id)->toBe($agentId)
        ->and($dto->object)->toBe('agent')
        ->and($dto->name)->toBe('Customer Support Agent')
        ->and($dto->model)->toBe('mistral-large-latest')
        ->and($dto->instructions)->toContain('customer support assistant')
        ->and($dto->description)->toBe('Handles customer inquiries and support tickets')
        ->and($dto->tools)->toBeArray()
        ->and($dto->tools)->toHaveCount(1)
        ->and($dto->temperature)->toBe(0.7)
        ->and($dto->topP)->toBe(0.9)
        ->and($dto->version)->toBe(1);
});

it('can update an agent', function () {
    Saloon::fake([
        UpdateAgentRequest::class => MockResponse::fixture('agents/update'),
    ]);

    $agentId = 'ag:123456:20241011:a1b2c3d4';
    $response = $this->mistral->agents()->update(
        $agentId,
        new AgentUpdateRequest(
            instructions: 'You are an expert customer support assistant. Always be polite, professional, and provide detailed solutions.',
            temperature: 0.8,
            topP: 0.95
        )
    );

    Saloon::assertSent(UpdateAgentRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(Agent::class)
        ->and($dto->id)->toBe($agentId)
        ->and($dto->instructions)->toContain('expert customer support assistant')
        ->and($dto->temperature)->toBe(0.8)
        ->and($dto->topP)->toBe(0.95)
        ->and($dto->version)->toBe(2);
});

it('can update agent with all parameters', function () {
    Saloon::fake([
        UpdateAgentRequest::class => MockResponse::fixture('agents/update'),
    ]);

    $agentId = 'ag:123456:20241011:a1b2c3d4';
    $response = $this->mistral->agents()->update(
        $agentId,
        new AgentUpdateRequest(
            name: 'Updated Agent Name',
            model: 'mistral-large-latest',
            instructions: 'Updated instructions',
            description: 'Updated description',
            tools: [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'new_tool',
                        'description' => 'A new tool',
                    ],
                ],
            ],
            temperature: 0.8,
            topP: 0.95
        )
    );

    Saloon::assertSent(UpdateAgentRequest::class);
    expect($response->status())->toBe(200);
});

it('can update agent version', function () {
    Saloon::fake([
        UpdateAgentVersionRequest::class => MockResponse::fixture('agents/updateVersion'),
    ]);

    $agentId = 'ag:123456:20241011:a1b2c3d4';
    $response = $this->mistral->agents()->updateVersion($agentId, version: 1);

    Saloon::assertSent(UpdateAgentVersionRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(Agent::class)
        ->and($dto->id)->toBe($agentId)
        ->and($dto->version)->toBe(1);
});

it('update creates new version', function () {
    Saloon::fake([
        UpdateAgentRequest::class => MockResponse::fixture('agents/update'),
    ]);

    $agentId = 'ag:123456:20241011:a1b2c3d4';
    $response = $this->mistral->agents()->update(
        $agentId,
        new AgentUpdateRequest(
            instructions: 'New instructions'
        )
    );

    $dto = $response->dto();
    expect($dto->version)->toBe(2);
});

it('agent DTO has correct property mappings', function () {
    Saloon::fake([
        GetAgentRequest::class => MockResponse::fixture('agents/get'),
    ]);

    $dto = $this->mistral->agents()->get('ag:123456:20241011:a1b2c3d4')->dto();

    expect($dto->createdAt)->toBeInt()
        ->and($dto->topP)->toBe(0.9);
});

it('can handle agents without optional fields', function () {
    Saloon::fake([
        GetAgentRequest::class => MockResponse::make([
            'id' => 'ag:minimal:test',
            'object' => 'agent',
            'created_at' => 1728648000,
            'name' => 'Minimal Agent',
            'model' => 'mistral-large-latest',
            'version' => 1,
        ]),
    ]);

    $dto = $this->mistral->agents()->get('ag:minimal:test')->dto();

    expect($dto)->toBeInstanceOf(Agent::class)
        ->and($dto->id)->toBe('ag:minimal:test')
        ->and($dto->name)->toBe('Minimal Agent')
        ->and($dto->instructions)->toBeNull()
        ->and($dto->description)->toBeNull()
        ->and($dto->tools)->toBeNull()
        ->and($dto->temperature)->toBeNull()
        ->and($dto->topP)->toBeNull()
        ->and($dto->version)->toBe(1);
});
