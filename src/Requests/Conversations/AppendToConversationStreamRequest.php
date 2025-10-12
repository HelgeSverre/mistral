<?php

namespace HelgeSverre\Mistral\Requests\Conversations;

use HelgeSverre\Mistral\Dto\Conversations\ConversationAppendRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class AppendToConversationStreamRequest extends Request implements HasBody
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
        $body = array_filter($this->appendRequest->toArray());
        $body['stream'] = true;

        return $body;
    }
}
