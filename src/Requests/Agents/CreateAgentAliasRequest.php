<?php

namespace HelgeSverre\Mistral\Requests\Agents;

use HelgeSverre\Mistral\Dto\Agents\AgentAliasResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Create or Update Agent Alias
 *
 * Create a new alias or update an existing alias to point to a specific version.
 * Aliases are unique per agent and can be reassigned to different versions.
 */
class CreateAgentAliasRequest extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        protected string $agentId,
        protected string $alias,
        protected int $version,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/agents/{$this->agentId}/aliases";
    }

    protected function defaultQuery(): array
    {
        return [
            'alias' => $this->alias,
            'version' => $this->version,
        ];
    }

    public function createDtoFromResponse(Response $response): AgentAliasResponse
    {
        return AgentAliasResponse::from($response->json());
    }
}
