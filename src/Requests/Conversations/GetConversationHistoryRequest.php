<?php

namespace HelgeSverre\Mistral\Requests\Conversations;

use HelgeSverre\Mistral\Dto\Conversations\ConversationHistory;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetConversationHistoryRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $conversationId) {}

    public function resolveEndpoint(): string
    {
        return "/conversations/{$this->conversationId}/history";
    }

    public function createDtoFromResponse(Response $response): ConversationHistory
    {
        return ConversationHistory::from($response->json());
    }
}
