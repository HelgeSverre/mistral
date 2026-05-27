<?php

namespace HelgeSverre\Mistral\Requests\Agents;

use HelgeSverre\Mistral\Dto\Agents\Agent;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Get Agent Version
 *
 * Get a specific agent version by version number.
 */
class GetAgentVersionRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $agentId,
        protected int $version,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/agents/{$this->agentId}/versions/{$this->version}";
    }

    public function createDtoFromResponse(Response $response): Agent
    {
        return Agent::from($response->json());
    }
}
