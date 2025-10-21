<?php

namespace HelgeSverre\Mistral\Requests\FineTuning;

use HelgeSverre\Mistral\Dto\FineTuning\ArchiveFTModelOut;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Archive fine-tuned model
 */
class ArchiveModelRequest extends Request
{
    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return "/fine_tuning/models/{$this->modelId}/archive";
    }

    public function __construct(
        protected string $modelId,
    ) {}

    public function createDtoFromResponse(Response $response): ArchiveFTModelOut
    {
        return ArchiveFTModelOut::from($response->json());
    }
}
