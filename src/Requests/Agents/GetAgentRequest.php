<?php

namespace HelgeSverre\Mistral\Requests\Agents;

use HelgeSverre\Mistral\Dto\Agents\Agent;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Get Agent
 *
 * Retrieve a specific agent by ID
 */
class GetAgentRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $agentId) {}

    public function resolveEndpoint(): string
    {
        return "/agents/{$this->agentId}";
    }

    public function createDtoFromResponse(Response $response): Agent
    {
        return Agent::from($response->json());
    }
}
