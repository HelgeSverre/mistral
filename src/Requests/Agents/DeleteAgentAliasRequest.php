<?php

namespace HelgeSverre\Mistral\Requests\Agents;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Delete Agent Alias
 *
 * Delete an existing alias for an agent. Returns 204 No Content.
 */
class DeleteAgentAliasRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected string $agentId,
        protected string $alias,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/agents/{$this->agentId}/aliases";
    }

    protected function defaultQuery(): array
    {
        return [
            'alias' => $this->alias,
        ];
    }
}
