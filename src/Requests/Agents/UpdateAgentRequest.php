<?php

namespace HelgeSverre\Mistral\Requests\Agents;

use HelgeSverre\Mistral\Dto\Agents\Agent;
use HelgeSverre\Mistral\Dto\Agents\AgentUpdateRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Update Agent
 *
 * Update an agent's configuration (creates new version)
 */
class UpdateAgentRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    public function __construct(
        protected string $agentId,
        protected AgentUpdateRequest $agentUpdateRequest
    ) {}

    public function resolveEndpoint(): string
    {
        return "/agents/{$this->agentId}";
    }

    protected function defaultBody(): array
    {
        return $this->agentUpdateRequest->toArray();
    }

    public function createDtoFromResponse(Response $response): Agent
    {
        return Agent::from($response->json());
    }
}
