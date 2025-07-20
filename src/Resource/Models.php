<?php

namespace HelgeSverre\Mistral\Resource;

use HelgeSverre\Mistral\Requests\Models\ListModels;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Models extends BaseResource
{
    public function list(): Response
    {
        return $this->connector->send(new ListModels);
    }
}
