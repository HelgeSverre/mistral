<?php

namespace HelgeSverre\Mistral\Requests\Conversations;

use HelgeSverre\Mistral\Dto\Conversations\ConversationResponse;
use HelgeSverre\Mistral\Dto\Conversations\ConversationRestartRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class RestartConversationRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $conversationId,
        protected ConversationRestartRequest $restartRequest
    ) {}

    public function resolveEndpoint(): string
    {
        return "/conversations/{$this->conversationId}/restart";
    }

    protected function defaultBody(): array
    {
        return array_filter($this->restartRequest->toArray());
    }

    public function createDtoFromResponse(Response $response): ConversationResponse
    {
        return ConversationResponse::from($response->json());
    }
}
