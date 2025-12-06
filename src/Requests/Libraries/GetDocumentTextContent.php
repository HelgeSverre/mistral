<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Requests\Libraries;

use HelgeSverre\Mistral\Dto\Libraries\DocumentTextContent;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetDocumentTextContent extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $libraryId,
        protected string $documentId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/libraries/{$this->libraryId}/documents/{$this->documentId}/text_content";
    }

    public function createDtoFromResponse(Response $response): DocumentTextContent
    {
        return DocumentTextContent::from($response->json());
    }
}
