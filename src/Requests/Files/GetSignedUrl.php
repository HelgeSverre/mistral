<?php

namespace HelgeSverre\Mistral\Requests\Files;

use HelgeSverre\Mistral\Dto\Files\FileSignedURL;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Get Signed URL
 *
 * Get temporary signed download URL for a file
 */
class GetSignedUrl extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return "/files/{$this->fileId}/url";
    }

    public function __construct(
        protected readonly string $fileId,
        protected readonly ?int $expiry = null
    ) {}

    protected function defaultQuery(): array
    {
        if ($this->expiry === null) {
            return [];
        }

        return ['expiry' => $this->expiry];
    }

    public function createDtoFromResponse(Response $response): FileSignedURL
    {
        return FileSignedURL::from($response->json());
    }
}
