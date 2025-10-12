<?php

namespace HelgeSverre\Mistral\Requests\Files;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Download File
 *
 * Download file content by file ID (returns binary data)
 */
class DownloadFile extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return "/files/{$this->fileId}/content";
    }

    public function __construct(protected readonly string $fileId) {}
}
