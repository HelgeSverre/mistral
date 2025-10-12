<?php

namespace HelgeSverre\Mistral\Requests\Files;

use HelgeSverre\Mistral\Dto\Files\RetrieveFileOut;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Retrieve File
 *
 * Get file metadata by file ID
 */
class RetrieveFile extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return "/files/{$this->fileId}";
    }

    public function __construct(protected readonly string $fileId) {}

    public function createDtoFromResponse(Response $response): RetrieveFileOut
    {
        return RetrieveFileOut::from($response->json());
    }
}
