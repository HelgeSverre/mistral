<?php

namespace HelgeSverre\Mistral\Resource;

use HelgeSverre\Mistral\Dto\Models\BaseModelCard;
use HelgeSverre\Mistral\Dto\Models\DeleteModelOut;
use HelgeSverre\Mistral\Dto\Models\FTModelCard;
use HelgeSverre\Mistral\Dto\Models\ModelList;
use HelgeSverre\Mistral\Requests\Models\DeleteModelRequest;
use HelgeSverre\Mistral\Requests\Models\ListModels;
use HelgeSverre\Mistral\Requests\Models\RetrieveModelRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Models extends BaseResource
{
    public function list(): Response
    {
        return $this->connector->send(new ListModels);
    }

    /**
     * List models and return typed DTO
     */
    public function listDto(): ModelList
    {
        return $this->list()->dto();
    }

    public function retrieve(string $modelId): BaseModelCard|FTModelCard
    {
        return $this->connector->send(new RetrieveModelRequest($modelId))
            ->dto();
    }

    public function delete(string $modelId): DeleteModelOut
    {
        return $this->connector->send(new DeleteModelRequest($modelId))
            ->dto();
    }
}
