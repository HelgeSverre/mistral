<?php

namespace HelgeSverre\Mistral\Requests\Conversations;

use HelgeSverre\Mistral\Dto\Conversations\ConversationRequest;
use HelgeSverre\Mistral\Dto\Conversations\ConversationResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreateConversationRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/conversations';
    }

    public function __construct(protected ConversationRequest $conversationRequest) {}

    protected function defaultBody(): array
    {
        return array_filter($this->conversationRequest->toArray());
    }

    public function createDtoFromResponse(Response $response): ConversationResponse
    {
        return ConversationResponse::from($response->json());
    }
}
