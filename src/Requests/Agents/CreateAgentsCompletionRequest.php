<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Requests\Agents;

use HelgeSverre\Mistral\Dto\Agents\AgentsCompletionRequest;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreateAgentsCompletionRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected AgentsCompletionRequest $request
    ) {}

    public function resolveEndpoint(): string
    {
        return '/agents/completions';
    }

    protected function defaultBody(): array
    {
        return array_filter($this->request->toArray(), fn ($v) => $v !== null);
    }

    public function createDtoFromResponse(Response $response): ChatCompletionResponse
    {
        return ChatCompletionResponse::from($response->json());
    }
}
