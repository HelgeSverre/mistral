<?php

namespace HelgeSverre\Mistral\Requests\OCR;

use HelgeSverre\Mistral\Dto\OCR\OCRRequest;
use HelgeSverre\Mistral\Dto\OCR\OCRResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * processDocument
 *
 * Process documents with OCR
 */
class ProcessDocument extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/ocr';
    }

    public function __construct(protected OCRRequest $ocrRequest)
    {
    }

    protected function defaultBody(): array
    {
        return array_filter($this->ocrRequest->toArray());
    }

    public function createDtoFromResponse(Response $response): OCRResponse
    {
        return OCRResponse::from($response->json());
    }
}
