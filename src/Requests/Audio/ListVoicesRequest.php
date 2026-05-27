<?php

namespace HelgeSverre\Mistral\Requests\Audio;

use HelgeSverre\Mistral\Dto\Audio\VoiceListResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * List Voices
 *
 * List all voices (excluding sample data) with pagination support.
 */
class ListVoicesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected ?int $limit = null,
        protected ?int $offset = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/audio/voices';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'limit' => $this->limit,
            'offset' => $this->offset,
        ], fn ($value) => $value !== null);
    }

    public function createDtoFromResponse(Response $response): VoiceListResponse
    {
        return VoiceListResponse::from($response->json());
    }
}
