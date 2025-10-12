<?php

namespace HelgeSverre\Mistral\Requests\Files;

use HelgeSverre\Mistral\Dto\Files\UploadFileOut;
use HelgeSverre\Mistral\Enums\FilePurpose;
use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasMultipartBody;

/**
 * Upload File
 *
 * Upload a training file for fine-tuning
 */
class UploadFile extends Request implements HasBody
{
    use HasMultipartBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/files';
    }

    public function __construct(
        protected readonly string $filePath,
        protected readonly ?FilePurpose $purpose = null
    ) {}

    protected function defaultBody(): array
    {
        $body = [
            new MultipartValue(
                name: 'file',
                value: file_get_contents($this->filePath),
                filename: basename($this->filePath)
            ),
        ];

        if ($this->purpose !== null) {
            $body[] = new MultipartValue(
                name: 'purpose',
                value: $this->purpose->value
            );
        }

        return $body;
    }

    public function createDtoFromResponse(Response $response): UploadFileOut
    {
        return UploadFileOut::from($response->json());
    }
}
