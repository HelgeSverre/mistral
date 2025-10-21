<?php

namespace HelgeSverre\Mistral\Requests\Audio;

use HelgeSverre\Mistral\Dto\Audio\TranscriptionResponse;
use HelgeSverre\Mistral\Enums\ResponseFormat;
use HelgeSverre\Mistral\Enums\TimestampGranularity;
use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasMultipartBody;

/**
 * Create Audio Transcription
 *
 * Transcribes audio into the input language
 */
class CreateTranscriptionRequest extends Request implements HasBody
{
    use HasMultipartBody;

    protected Method $method = Method::POST;

    /**
     * @param  string  $filePath  Path to the audio file to transcribe
     * @param  string  $model  ID of the model to use (e.g., "whisper-large-v3")
     * @param  string|null  $language  The language of the input audio (ISO-639-1 format)
     * @param  string|null  $prompt  Optional text to guide the model's style
     * @param  ResponseFormat|null  $responseFormat  Format of the output (json, text, verbose_json)
     * @param  float|null  $temperature  Sampling temperature between 0 and 1
     * @param  array<TimestampGranularity>|null  $timestampGranularities  Timestamp granularities to include
     */
    public function __construct(
        protected readonly string $filePath,
        protected readonly string $model,
        protected readonly ?string $language = null,
        protected readonly ?string $prompt = null,
        protected readonly ?ResponseFormat $responseFormat = null,
        protected readonly ?float $temperature = null,
        protected readonly ?array $timestampGranularities = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/audio/transcriptions';
    }

    protected function defaultBody(): array
    {
        $body = [
            new MultipartValue(
                name: 'file',
                value: file_get_contents($this->filePath),
                filename: basename($this->filePath)
            ),
            new MultipartValue(
                name: 'model',
                value: $this->model
            ),
        ];

        if ($this->language !== null) {
            $body[] = new MultipartValue(
                name: 'language',
                value: $this->language
            );
        }

        if ($this->prompt !== null) {
            $body[] = new MultipartValue(
                name: 'prompt',
                value: $this->prompt
            );
        }

        if ($this->responseFormat !== null) {
            $body[] = new MultipartValue(
                name: 'response_format',
                value: $this->responseFormat->value
            );
        }

        if ($this->temperature !== null) {
            $body[] = new MultipartValue(
                name: 'temperature',
                value: (string) $this->temperature
            );
        }

        if ($this->timestampGranularities !== null && count($this->timestampGranularities) > 0) {
            $granularities = array_map(fn (TimestampGranularity $g) => $g->value, $this->timestampGranularities);
            $body[] = new MultipartValue(
                name: 'timestamp_granularities[]',
                value: json_encode($granularities)
            );
        }

        return $body;
    }

    public function createDtoFromResponse(Response $response): TranscriptionResponse
    {
        return TranscriptionResponse::from($response->json());
    }
}
