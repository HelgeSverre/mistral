<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Requests\Libraries;

use HelgeSverre\Mistral\Dto\Libraries\ListDocumentOut;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListDocuments extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $libraryId,
        protected ?string $search = null,
        protected ?int $page = null,
        protected ?int $pageSize = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/v1/libraries/{$this->libraryId}/documents";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'search' => $this->search,
            'page' => $this->page,
            'page_size' => $this->pageSize,
        ], fn ($value) => $value !== null);
    }

    public function createDtoFromResponse(Response $response): ListDocumentOut
    {
        return ListDocumentOut::from($response->json());
    }
}
