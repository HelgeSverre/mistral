<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Requests\Libraries;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteDocument extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected string $libraryId,
        protected string $documentId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/v1/libraries/{$this->libraryId}/documents/{$this->documentId}";
    }
}
