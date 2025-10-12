<?php

namespace HelgeSverre\Mistral\Requests\Conversations;

use HelgeSverre\Mistral\Dto\Conversations\ConversationAppendRequest;
use HelgeSverre\Mistral\Dto\Conversations\ConversationResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class AppendToConversationRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $conversationId,
        protected ConversationAppendRequest $appendRequest
    ) {}

    public function resolveEndpoint(): string
    {
        return "/conversations/{$this->conversationId}";
    }

    protected function defaultBody(): array
    {
        return array_filter($this->appendRequest->toArray());
    }

    public function createDtoFromResponse(Response $response): ConversationResponse
    {
        return ConversationResponse::from($response->json());
    }
}
