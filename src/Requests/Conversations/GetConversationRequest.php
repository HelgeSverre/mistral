<?php

namespace HelgeSverre\Mistral\Requests\Conversations;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetConversationRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $conversationId) {}

    public function resolveEndpoint(): string
    {
        return "/conversations/{$this->conversationId}";
    }
}
