<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Requests\Libraries;

use HelgeSverre\Mistral\Dto\Libraries\ListLibraryOut;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListLibraries extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ?int $page = null,
        protected ?int $pageSize = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/v1/libraries';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'page' => $this->page,
            'page_size' => $this->pageSize,
        ], fn ($value) => $value !== null);
    }

    public function createDtoFromResponse(Response $response): ListLibraryOut
    {
        return ListLibraryOut::from($response->json());
    }
}
