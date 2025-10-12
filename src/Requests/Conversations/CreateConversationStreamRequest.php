<?php

namespace HelgeSverre\Mistral\Requests\Conversations;

use HelgeSverre\Mistral\Dto\Conversations\ConversationRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateConversationStreamRequest extends Request implements HasBody
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
        $body = array_filter($this->conversationRequest->toArray());
        $body['stream'] = true;

        return $body;
    }
}
