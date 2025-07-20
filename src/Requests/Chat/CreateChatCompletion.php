<?php

namespace HelgeSverre\Mistral\Requests\Chat;

use HelgeSverre\Mistral\Dto\Chat\ChatCompletionRequest;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * createChatCompletion
 *
 * Create Chat Completions
 */
class CreateChatCompletion extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/chat/completions';
    }

    public function __construct(protected ChatCompletionRequest $chatCompletionRequest) {}

    protected function defaultBody(): array
    {
        return array_filter($this->chatCompletionRequest->toArray());
    }

    public function createDtoFromResponse(Response $response): ChatCompletionResponse
    {
        return ChatCompletionResponse::from($response->json());
    }
}
