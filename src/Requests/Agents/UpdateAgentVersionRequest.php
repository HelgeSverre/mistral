<?php

namespace HelgeSverre\Mistral\Requests\Agents;

use HelgeSverre\Mistral\Dto\Agents\Agent;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Update Agent Version
 *
 * Switch agent to a specific version
 */
class UpdateAgentVersionRequest extends Request
{
    protected Method $method = Method::PATCH;

    public function __construct(
        protected string $agentId,
        protected int $version
    ) {}

    public function resolveEndpoint(): string
    {
        return "/agents/{$this->agentId}/version";
    }

    protected function defaultQuery(): array
    {
        return [
            'version' => $this->version,
        ];
    }

    public function createDtoFromResponse(Response $response): Agent
    {
        return Agent::from($response->json());
    }
}
