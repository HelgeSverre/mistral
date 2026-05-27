<?php

namespace HelgeSverre\Mistral\Requests\Agents;

use HelgeSverre\Mistral\Dto\Agents\AgentAliasResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Spatie\LaravelData\DataCollection;

/**
 * List Agent Aliases
 *
 * Retrieve all version aliases for a specific agent.
 */
class ListAgentAliasesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $agentId) {}

    public function resolveEndpoint(): string
    {
        return "/agents/{$this->agentId}/aliases";
    }

    public function createDtoFromResponse(Response $response): DataCollection
    {
        return AgentAliasResponse::collect($response->json(), DataCollection::class);
    }
}
