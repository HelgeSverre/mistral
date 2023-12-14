<?php

namespace HelgeSverre\Mistral\Requests\Embedding;

use HelgeSverre\Mistral\Dto\Embedding\EmbeddingRequest;
use HelgeSverre\Mistral\Dto\Embedding\EmbeddingResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * createEmbedding
 *
 * Create Embeddings
 */
class CreateEmbedding extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/embeddings';
    }

    public function __construct(protected EmbeddingRequest $embeddingRequest)
    {
    }

    protected function defaultBody(): array
    {
        return $this->embeddingRequest->toArray();
    }

    public function createDtoFromResponse(Response $response): EmbeddingResponse
    {
        return EmbeddingResponse::from($response->json());
    }
}
