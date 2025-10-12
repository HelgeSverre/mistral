<?php

namespace HelgeSverre\Mistral\Requests\FineTuning;

use HelgeSverre\Mistral\Dto\FineTuning\UnarchiveFTModelOut;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Unarchive fine-tuned model
 */
class UnarchiveModelRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function resolveEndpoint(): string
    {
        return "/fine_tuning/models/{$this->modelId}/archive";
    }

    public function __construct(
        protected string $modelId,
    ) {}

    public function createDtoFromResponse(Response $response): UnarchiveFTModelOut
    {
        return UnarchiveFTModelOut::from($response->json());
    }
}
