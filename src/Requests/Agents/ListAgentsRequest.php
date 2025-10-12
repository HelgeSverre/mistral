<?php

namespace HelgeSverre\Mistral\Requests\Agents;

use HelgeSverre\Mistral\Dto\Agents\AgentList;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * List Agents
 *
 * List all agents with pagination support
 */
class ListAgentsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/agents';
    }

    public function __construct(
        protected ?int $page = null,
        protected ?int $pageSize = null,
    ) {}

    protected function defaultQuery(): array
    {
        return array_filter([
            'page' => $this->page,
            'page_size' => $this->pageSize,
        ], fn ($value) => $value !== null);
    }

    public function createDtoFromResponse(Response $response): AgentList
    {
        return AgentList::from($response->json());
    }
}
