<?php

namespace HelgeSverre\Mistral\Resource;

use HelgeSverre\Mistral\Dto\Embedding\EmbeddingRequest;
use HelgeSverre\Mistral\Dto\Embedding\EmbeddingResponse;
use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Requests\Embedding\CreateEmbedding;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Embedding extends BaseResource
{
    public function create(
        array $input,
        string $model = Model::embed->value,
        string $encodingFormat = 'float'
    ): Response {
        return $this->connector->send(new CreateEmbedding(
            new EmbeddingRequest(
                model: $model,
                input: $input,
                encodingFormat: $encodingFormat,
            )
        ));
    }

    /**
     * Create embeddings and return typed DTO
     */
    public function createDto(
        array $input,
        string $model = Model::embed->value,
        string $encodingFormat = 'float'
    ): EmbeddingResponse {
        return $this->create($input, $model, $encodingFormat)->dto();
    }
}
