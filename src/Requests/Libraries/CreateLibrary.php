<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Requests\Libraries;

use HelgeSverre\Mistral\Dto\Libraries\LibraryIn;
use HelgeSverre\Mistral\Dto\Libraries\LibraryOut;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreateLibrary extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected LibraryIn $library,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/v1/libraries';
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
