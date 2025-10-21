<?php

namespace HelgeSverre\Mistral\Requests\Models;

use HelgeSverre\Mistral\Dto\Models\DeleteModelOut;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * deleteModel
 *
 * Delete a fine-tuned model
 */
class DeleteModelRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        public readonly string $modelId
    ) {}

    public function resolveEndpoint(): string
    {
        return '/v1/models/'.$this->modelId;
    }

    public function createDtoFromResponse(Response $response): DeleteModelOut
    {
        return DeleteModelOut::from($response->json());
    }
}
