<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Requests\Libraries;

use HelgeSverre\Mistral\Dto\Libraries\SharingIn;
use HelgeSverre\Mistral\Dto\Libraries\SharingOut;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreateSharing extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected string $libraryId,
        protected SharingIn $sharing,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/v1/libraries/{$this->libraryId}/share";
    }

    protected function defaultBody(): array
    {
        return $this->sharing->toArray();
    }

    public function createDtoFromResponse(Response $response): SharingOut
    {
        return SharingOut::from($response->json());
    }
}
