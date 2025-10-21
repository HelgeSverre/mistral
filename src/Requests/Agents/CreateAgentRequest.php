<?php

namespace HelgeSverre\Mistral\Requests\Agents;

use HelgeSverre\Mistral\Dto\Agents\Agent;
use HelgeSverre\Mistral\Dto\Agents\AgentCreationRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Create Agent
 *
 * Create a new agent with custom instructions and tools
 */
class CreateAgentRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/agents';
    }

    public function __construct(protected AgentCreationRequest $agentCreationRequest) {}

    protected function defaultBody(): array
    {
        return $this->agentCreationRequest->toArray();
    }

    public function createDtoFromResponse(Response $response): Agent
    {
        return Agent::from($response->json());
    }
}
