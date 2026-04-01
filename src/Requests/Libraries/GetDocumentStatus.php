<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Requests\Libraries;

use HelgeSverre\Mistral\Dto\Libraries\ProcessingStatusOut;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetDocumentStatus extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $libraryId,
        protected string $documentId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/libraries/{$this->libraryId}/documents/{$this->documentId}/status";
    }

    public function createDtoFromResponse(Response $response): ProcessingStatusOut
    {
        return ProcessingStatusOut::from($response->json());
    }
}
