<?php

namespace HelgeSverre\Mistral\Resource;

use Generator;
use HelgeSverre\Mistral\Concerns\HandlesStreamedResponses;
use HelgeSverre\Mistral\Dto\Agents\Agent;
use HelgeSverre\Mistral\Dto\Agents\AgentAliasResponse;
use HelgeSverre\Mistral\Dto\Agents\AgentCreationRequest;
use HelgeSverre\Mistral\Dto\Agents\AgentList;
use HelgeSverre\Mistral\Dto\Agents\AgentsCompletionRequest;
use HelgeSverre\Mistral\Dto\Agents\AgentUpdateRequest;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionResponse;
use HelgeSverre\Mistral\Dto\Chat\StreamedChatCompletionResponse;
use HelgeSverre\Mistral\Requests\Agents\CreateAgentAliasRequest;
use HelgeSverre\Mistral\Requests\Agents\CreateAgentRequest;
use HelgeSverre\Mistral\Requests\Agents\CreateAgentsCompletionRequest;
use HelgeSverre\Mistral\Requests\Agents\DeleteAgentAliasRequest;
use HelgeSverre\Mistral\Requests\Agents\GetAgentRequest;
use HelgeSverre\Mistral\Requests\Agents\GetAgentVersionRequest;
use HelgeSverre\Mistral\Requests\Agents\ListAgentAliasesRequest;
use HelgeSverre\Mistral\Requests\Agents\ListAgentsRequest;
use HelgeSverre\Mistral\Requests\Agents\ListAgentVersionsRequest;
use HelgeSverre\Mistral\Requests\Agents\UpdateAgentRequest;
use HelgeSverre\Mistral\Requests\Agents\UpdateAgentVersionRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;
use Spatie\LaravelData\DataCollection;

class Agents extends BaseResource
{
    use HandlesStreamedResponses;

    /**
     * Create a new agent
     */
    public function create(AgentCreationRequest $request): Response
    {
        return $this->connector->send(new CreateAgentRequest($request));
    }

    /**
     * Create a new agent and return typed DTO
     */
    public function createDto(AgentCreationRequest $request): Agent
    {
        return $this->create($request)->dto();
    }

    /**
     * List all agents with pagination
     */
    public function list(?int $page = null, ?int $pageSize = null): Response
    {
        return $this->connector->send(new ListAgentsRequest($page, $pageSize));
    }

    /**
     * List all agents with pagination and return typed DTO
     */
    public function listDto(?int $page = null, ?int $pageSize = null): AgentList
    {
        return $this->list($page, $pageSize)->dto();
    }

    /**
     * Retrieve a specific agent by ID
     */
    public function get(string $agentId): Response
    {
        return $this->connector->send(new GetAgentRequest($agentId));
    }

    /**
     * Retrieve a specific agent by ID and return typed DTO
     */
    public function getDto(string $agentId): Agent
    {
        return $this->get($agentId)->dto();
    }

    /**
     * Update an agent (creates new version)
     */
    public function update(string $agentId, AgentUpdateRequest $request): Response
    {
        return $this->connector->send(new UpdateAgentRequest($agentId, $request));
    }

    /**
     * Update an agent (creates new version) and return typed DTO
     */
    public function updateDto(string $agentId, AgentUpdateRequest $request): Agent
    {
        return $this->update($agentId, $request)->dto();
    }

    /**
     * Switch agent to a specific version
     */
    public function updateVersion(string $agentId, int $version): Response
    {
        return $this->connector->send(new UpdateAgentVersionRequest($agentId, $version));
    }

    /**
     * Switch agent to a specific version and return typed DTO
     */
    public function updateVersionDto(string $agentId, int $version): Agent
    {
        return $this->updateVersion($agentId, $version)->dto();
    }

    /**
     * Run completion using an agent
     */
    public function complete(AgentsCompletionRequest $request): Response
    {
        return $this->connector->send(new CreateAgentsCompletionRequest($request));
    }

    /**
     * Run completion using an agent and return typed DTO
     */
    public function completeDto(AgentsCompletionRequest $request): ChatCompletionResponse
    {
        return $this->complete($request)->dto();
    }

    /**
     * Run streamed completion using an agent
     *
     * @return Generator<StreamedChatCompletionResponse>
     */
    public function completeStreamed(AgentsCompletionRequest $request): Generator
    {
        $streamRequest = new AgentsCompletionRequest(
            agentId: $request->agentId,
            messages: $request->messages,
            maxTokens: $request->maxTokens,
            stream: true,
            stop: $request->stop,
            randomSeed: $request->randomSeed,
            responseFormat: $request->responseFormat,
            tools: $request->tools,
            toolChoice: $request->toolChoice,
            presencePenalty: $request->presencePenalty,
            frequencyPenalty: $request->frequencyPenalty,
            n: $request->n,
            prediction: $request->prediction,
            parallelToolCalls: $request->parallelToolCalls,
            promptMode: $request->promptMode,
        );

        $response = $this->connector->send(new CreateAgentsCompletionRequest($streamRequest));

        foreach ($this->getStreamIterator($response->stream()) as $chatResponse) {
            yield StreamedChatCompletionResponse::from($chatResponse);
        }
    }

    /**
     * List all versions of an agent.
     */
    public function listVersions(string $agentId, ?int $page = null, ?int $pageSize = null): Response
    {
        return $this->connector->send(new ListAgentVersionsRequest($agentId, $page, $pageSize));
    }

    /**
     * List all versions of an agent and return typed DTO collection.
     *
     * @return DataCollection<int, Agent>
     */
    public function listVersionsDto(string $agentId, ?int $page = null, ?int $pageSize = null): DataCollection
    {
        return $this->listVersions($agentId, $page, $pageSize)->dto();
    }

    /**
     * Get a specific agent version by version number.
     */
    public function getVersion(string $agentId, int $version): Response
    {
        return $this->connector->send(new GetAgentVersionRequest($agentId, $version));
    }

    /**
     * Get a specific agent version by version number and return typed DTO.
     */
    public function getVersionDto(string $agentId, int $version): Agent
    {
        return $this->getVersion($agentId, $version)->dto();
    }

    /**
     * List all aliases for an agent.
     */
    public function listAliases(string $agentId): Response
    {
        return $this->connector->send(new ListAgentAliasesRequest($agentId));
    }

    /**
     * List all aliases for an agent and return typed DTO collection.
     *
     * @return DataCollection<int, AgentAliasResponse>
     */
    public function listAliasesDto(string $agentId): DataCollection
    {
        return $this->listAliases($agentId)->dto();
    }

    /**
     * Create or update an agent version alias.
     */
    public function createAlias(string $agentId, string $alias, int $version): Response
    {
        return $this->connector->send(new CreateAgentAliasRequest($agentId, $alias, $version));
    }

    /**
     * Create or update an agent version alias and return typed DTO.
     */
    public function createAliasDto(string $agentId, string $alias, int $version): AgentAliasResponse
    {
        return $this->createAlias($agentId, $alias, $version)->dto();
    }

    /**
     * Delete an agent version alias. Returns 204 No Content.
     */
    public function deleteAlias(string $agentId, string $alias): Response
    {
        return $this->connector->send(new DeleteAgentAliasRequest($agentId, $alias));
    }
}
