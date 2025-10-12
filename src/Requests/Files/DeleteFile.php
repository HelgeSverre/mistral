<?php

namespace HelgeSverre\Mistral\Requests\Files;

use HelgeSverre\Mistral\Dto\Files\DeleteFileOut;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Delete File
 *
 * Delete a file by file ID
 */
class DeleteFile extends Request
{
    protected Method $method = Method::DELETE;

    public function resolveEndpoint(): string
    {
        return "/files/{$this->fileId}";
    }

    public function __construct(protected readonly string $fileId) {}

    public function createDtoFromResponse(Response $response): DeleteFileOut
    {
        return DeleteFileOut::from($response->json());
    }
}
