<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Requests\Libraries;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteLibrary extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected string $libraryId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/v1/libraries/{$this->libraryId}";
    }
}
