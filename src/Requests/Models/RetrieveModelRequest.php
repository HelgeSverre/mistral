<?php

namespace HelgeSverre\Mistral\Requests\Models;

use HelgeSverre\Mistral\Dto\Models\BaseModelCard;
use HelgeSverre\Mistral\Dto\Models\FTModelCard;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * retrieveModel
 *
 * Retrieve a model information
 */
class RetrieveModelRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly string $modelId
    ) {}

    public function resolveEndpoint(): string
    {
        return '/v1/models/'.$this->modelId;
    }

    public function createDtoFromResponse(Response $response): BaseModelCard|FTModelCard
    {
        $data = $response->json();

        // Discriminate based on the 'type' field
        if (isset($data['type']) && $data['type'] === 'fine-tuned') {
            return FTModelCard::from($data);
        }

        return BaseModelCard::from($data);
    }
}
