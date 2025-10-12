<?php

namespace HelgeSverre\Mistral\Resource;

use HelgeSverre\Mistral\Dto\Agents\AgentCreationRequest;
use HelgeSverre\Mistral\Dto\Agents\AgentUpdateRequest;
use HelgeSverre\Mistral\Requests\Agents\CreateAgentRequest;
use HelgeSverre\Mistral\Requests\Agents\GetAgentRequest;
use HelgeSverre\Mistral\Requests\Agents\ListAgentsRequest;
use HelgeSverre\Mistral\Requests\Agents\UpdateAgentRequest;
use HelgeSverre\Mistral\Requests\Agents\UpdateAgentVersionRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Agents extends BaseResource
{
    /**
     * Create a new agent
     */
    public function create(AgentCreationRequest $request): Response
    {
        return $this->connector->send(new CreateAgentRequest($request));
    }

    /**
     * List all agents with pagination
     */
    public function list(?int $page = null, ?int $pageSize = null): Response
    {
        return $this->connector->send(new ListAgentsRequest($page, $pageSize));
    }

    /**
     * Retrieve a specific agent by ID
     */
    public function get(string $agentId): Response
    {
        return $this->connector->send(new GetAgentRequest($agentId));
    }

    /**
     * Update an agent (creates new version)
     */
    public function update(string $agentId, AgentUpdateRequest $request): Response
    {
        return $this->connector->send(new UpdateAgentRequest($agentId, $request));
    }

    /**
     * Switch agent to a specific version
     */
    public function updateVersion(string $agentId, int $version): Response
    {
        return $this->connector->send(new UpdateAgentVersionRequest($agentId, $version));
    }
}
