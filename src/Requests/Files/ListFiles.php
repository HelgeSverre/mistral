<?php

namespace HelgeSverre\Mistral\Requests\Files;

use HelgeSverre\Mistral\Dto\Files\ListFilesOut;
use HelgeSverre\Mistral\Dto\Files\ListFilesRequest;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * List Files
 *
 * List all uploaded files
 */
class ListFiles extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/files';
    }

    public function __construct(protected ListFilesRequest $listFilesRequest) {}

    protected function defaultQuery(): array
    {
        return $this->listFilesRequest->toArray();
    }

    public function createDtoFromResponse(Response $response): ListFilesOut
    {
        return ListFilesOut::from($response->json());
    }
}
