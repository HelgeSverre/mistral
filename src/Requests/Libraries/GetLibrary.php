<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Requests\Libraries;

use HelgeSverre\Mistral\Dto\Libraries\LibraryOut;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetLibrary extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $libraryId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/v1/libraries/{$this->libraryId}";
    }

    public function createDtoFromResponse(Response $response): LibraryOut
    {
        return LibraryOut::from($response->json());
    }
}
