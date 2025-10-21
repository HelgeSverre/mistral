<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Requests\Libraries;

use HelgeSverre\Mistral\Dto\Libraries\DocumentOut;
use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasMultipartBody;

class UploadDocument extends Request implements HasBody
{
    use HasMultipartBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected string $libraryId,
        protected string $filePath,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/v1/libraries/{$this->libraryId}/documents";
    }

    protected function defaultBody(): array
    {
        return [
            new MultipartValue(
                name: 'file',
                value: fopen($this->filePath, 'r'),
                filename: basename($this->filePath),
            ),
        ];
    }

    public function createDtoFromResponse(Response $response): DocumentOut
    {
        return DocumentOut::from($response->json());
    }
}
