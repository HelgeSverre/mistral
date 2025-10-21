<?php

namespace HelgeSverre\Mistral\Requests\Fim;

use HelgeSverre\Mistral\Dto\Fim\FIMCompletionRequest;
use HelgeSverre\Mistral\Dto\Fim\FIMCompletionResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * createFIMCompletion
 *
 * Create Fill-In-Middle Completions for code generation
 */
class CreateFIMCompletionRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/fim/completions';
    }

    public function __construct(protected FIMCompletionRequest $fimCompletionRequest) {}

    protected function defaultBody(): array
    {
        return array_filter($this->fimCompletionRequest->toArray());
    }

    public function createDtoFromResponse(Response $response): FIMCompletionResponse
    {
        return FIMCompletionResponse::from($response->json());
    }
}
