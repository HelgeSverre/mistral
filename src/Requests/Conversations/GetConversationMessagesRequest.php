<?php

namespace HelgeSverre\Mistral\Requests\Conversations;

use HelgeSverre\Mistral\Dto\Conversations\ConversationMessages;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetConversationMessagesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $conversationId) {}

    public function resolveEndpoint(): string
    {
        return "/conversations/{$this->conversationId}/messages";
    }

    public function createDtoFromResponse(Response $response): ConversationMessages
    {
        return ConversationMessages::from($response->json());
    }
}
