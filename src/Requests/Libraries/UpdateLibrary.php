<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Requests\Libraries;

use HelgeSverre\Mistral\Dto\Libraries\LibraryInUpdate;
use HelgeSverre\Mistral\Dto\Libraries\LibraryOut;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class UpdateLibrary extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected string $libraryId,
        protected LibraryInUpdate $library,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/v1/libraries/{$this->libraryId}";
    }

    protected function defaultBody(): array
    {
        return $this->library->toArray();
    }

    public function createDtoFromResponse(Response $response): LibraryOut
    {
        return LibraryOut::from($response->json());
    }
}
