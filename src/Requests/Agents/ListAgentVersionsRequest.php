<?php

namespace HelgeSverre\Mistral\Requests\Agents;

use HelgeSverre\Mistral\Dto\Agents\Agent;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Spatie\LaravelData\DataCollection;

/**
 * List Agent Versions
 *
 * Retrieve all versions for a specific agent with full agent context. Supports pagination.
 */
class ListAgentVersionsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $agentId,
        protected ?int $page = null,
        protected ?int $pageSize = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/agents/{$this->agentId}/versions";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'page' => $this->page,
            'page_size' => $this->pageSize,
        ], fn ($value) => $value !== null);
    }

    public function createDtoFromResponse(Response $response): DataCollection
    {
        return Agent::collect($response->json(), DataCollection::class);
    }
}
