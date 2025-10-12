<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Requests\Libraries;

use HelgeSverre\Mistral\Dto\Libraries\DocumentOut;
use HelgeSverre\Mistral\Dto\Libraries\DocumentUpdateIn;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class UpdateDocument extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected string $libraryId,
        protected string $documentId,
        protected DocumentUpdateIn $update,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/v1/libraries/{$this->libraryId}/documents/{$this->documentId}";
    }

    protected function defaultBody(): array
    {
        return $this->update->toArray();
    }

    public function createDtoFromResponse(Response $response): DocumentOut
    {
        return DocumentOut::from($response->json());
    }
}
