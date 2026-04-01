<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Requests\Libraries;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetExtractedTextSignedUrl extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $libraryId,
        protected string $documentId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/libraries/{$this->libraryId}/documents/{$this->documentId}/extracted-text-signed-url";
    }
}
