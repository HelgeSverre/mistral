<?php

namespace HelgeSverre\Mistral\Resource;

use Generator;
use HelgeSverre\Mistral\Concerns\HandlesStreamedResponses;
use HelgeSverre\Mistral\Dto\Audio\TranscriptionResponse;
use HelgeSverre\Mistral\Enums\ResponseFormat;
use HelgeSverre\Mistral\Enums\TimestampGranularity;
use HelgeSverre\Mistral\Requests\Audio\CreateTranscriptionRequest;
use HelgeSverre\Mistral\Requests\Audio\CreateTranscriptionStreamRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Audio extends BaseResource
{
    use HandlesStreamedResponses;

    /**
     * Transcribe an audio file to text
     *
     * @param  string  $filePath  Path to the audio file
     * @param  string  $model  Model to use (e.g., "whisper-large-v3")
     * @param  string|null  $language  Language code (ISO-639-1)
     * @param  string|null  $prompt  Optional prompt to guide transcription
     * @param  ResponseFormat|null  $responseFormat  Output format (json, text, verbose_json)
     * @param  float|null  $temperature  Sampling temperature (0-1)
     * @param  array<TimestampGranularity>|null  $timestampGranularities  Timestamp detail levels
     */
    public function transcribe(
        string $filePath,
        string $model,
        ?string $language = null,
        ?string $prompt = null,
        ?ResponseFormat $responseFormat = null,
        ?float $temperature = null,
        ?array $timestampGranularities = null,
    ): Response {
        return $this->connector->send(
            new CreateTranscriptionRequest(
                filePath: $filePath,
                model: $model,
                language: $language,
                prompt: $prompt,
                responseFormat: $responseFormat,
                temperature: $temperature,
                timestampGranularities: $timestampGranularities,
            )
        );
    }

    /**
     * Transcribe an audio file to text and return typed DTO
     */
    public function transcribeDto(
        string $filePath,
        string $model,
        ?string $language = null,
        ?string $prompt = null,
        ?ResponseFormat $responseFormat = null,
        ?float $temperature = null,
        ?array $timestampGranularities = null,
    ): TranscriptionResponse {
        return $this->transcribe($filePath, $model, $language, $prompt, $responseFormat, $temperature, $timestampGranularities)->dto();
    }

    /**
     * Transcribe an audio file with streaming response
     *
     * @param  string  $filePath  Path to the audio file
     * @param  string  $model  Model to use (e.g., "whisper-large-v3")
     * @param  string|null  $language  Language code (ISO-639-1)
     * @param  string|null  $prompt  Optional prompt to guide transcription
     * @param  ResponseFormat|null  $responseFormat  Output format (json, text, verbose_json)
     * @param  float|null  $temperature  Sampling temperature (0-1)
     * @param  array<TimestampGranularity>|null  $timestampGranularities  Timestamp detail levels
     * @return Generator<array<string, mixed>>
     */
    public function transcribeStreamed(
        string $filePath,
        string $model,
        ?string $language = null,
        ?string $prompt = null,
        ?ResponseFormat $responseFormat = null,
        ?float $temperature = null,
        ?array $timestampGranularities = null,
    ): Generator {
        $response = $this->connector->send(
            new CreateTranscriptionStreamRequest(
                filePath: $filePath,
                model: $model,
                language: $language,
                prompt: $prompt,
                responseFormat: $responseFormat,
                temperature: $temperature,
                timestampGranularities: $timestampGranularities,
            )
        );

        yield from $this->getStreamIterator($response->stream());
    }
}
