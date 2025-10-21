<?php

namespace HelgeSverre\Mistral\Requests\Conversations;

use HelgeSverre\Mistral\Dto\Conversations\ConversationList;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListConversationsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/conversations';
    }

    public function __construct(
        protected ?int $page = null,
        protected ?int $pageSize = null,
        protected ?string $order = null,
    ) {}

    protected function defaultQuery(): array
    {
        return array_filter([
            'page' => $this->page,
            'page_size' => $this->pageSize,
            'order' => $this->order,
        ]);
    }

    public function createDtoFromResponse(Response $response): ConversationList
    {
        return ConversationList::from($response->json());
    }
}
