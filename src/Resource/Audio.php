<?php

namespace HelgeSverre\Mistral\Resource;

use Generator;
use HelgeSverre\Mistral\Concerns\HandlesStreamedResponses;
use HelgeSverre\Mistral\Dto\Audio\SpeechRequest;
use HelgeSverre\Mistral\Dto\Audio\SpeechResponse;
use HelgeSverre\Mistral\Dto\Audio\SpeechStreamAudioDelta;
use HelgeSverre\Mistral\Dto\Audio\SpeechStreamDone;
use HelgeSverre\Mistral\Dto\Audio\TranscriptionResponse;
use HelgeSverre\Mistral\Dto\Audio\VoiceCreateRequest as VoiceCreateData;
use HelgeSverre\Mistral\Dto\Audio\VoiceListResponse;
use HelgeSverre\Mistral\Dto\Audio\VoiceResponse;
use HelgeSverre\Mistral\Dto\Audio\VoiceUpdateRequest as VoiceUpdateData;
use HelgeSverre\Mistral\Enums\ResponseFormat;
use HelgeSverre\Mistral\Enums\TimestampGranularity;
use HelgeSverre\Mistral\Requests\Audio\CreateSpeechRequest;
use HelgeSverre\Mistral\Requests\Audio\CreateSpeechStreamRequest;
use HelgeSverre\Mistral\Requests\Audio\CreateTranscriptionRequest;
use HelgeSverre\Mistral\Requests\Audio\CreateTranscriptionStreamRequest;
use HelgeSverre\Mistral\Requests\Audio\CreateVoiceRequest;
use HelgeSverre\Mistral\Requests\Audio\DeleteVoiceRequest;
use HelgeSverre\Mistral\Requests\Audio\GetVoiceRequest;
use HelgeSverre\Mistral\Requests\Audio\GetVoiceSampleRequest;
use HelgeSverre\Mistral\Requests\Audio\ListVoicesRequest;
use HelgeSverre\Mistral\Requests\Audio\UpdateVoiceRequest;
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

    /**
     * Generate speech audio from text.
     */
    public function speech(SpeechRequest $request): Response
    {
        return $this->connector->send(new CreateSpeechRequest($request));
    }

    /**
     * Generate speech audio from text and return typed DTO.
     */
    public function speechDto(SpeechRequest $request): SpeechResponse
    {
        return $this->speech($request)->dto();
    }

    /**
     * Generate speech audio from text and stream audio chunks back.
     *
     * Yields SpeechStreamAudioDelta for each audio chunk and a final SpeechStreamDone with usage info.
     *
     * @return Generator<SpeechStreamAudioDelta|SpeechStreamDone>
     */
    public function speechStreamed(SpeechRequest $request): Generator
    {
        $response = $this->connector->send(new CreateSpeechStreamRequest($request));

        foreach ($this->getStreamIterator($response->stream()) as $event) {
            $type = $event['type'] ?? null;

            if ($type === 'speech.audio.delta') {
                yield SpeechStreamAudioDelta::from($event);
            } elseif ($type === 'speech.audio.done') {
                yield SpeechStreamDone::from($event);
            }
        }
    }

    /**
     * List all voices (excluding sample data).
     */
    public function listVoices(?int $limit = null, ?int $offset = null): Response
    {
        return $this->connector->send(new ListVoicesRequest($limit, $offset));
    }

    /**
     * List all voices (excluding sample data) and return typed DTO.
     */
    public function listVoicesDto(?int $limit = null, ?int $offset = null): VoiceListResponse
    {
        return $this->listVoices($limit, $offset)->dto();
    }

    /**
     * Create a new voice with a base64-encoded audio sample.
     */
    public function createVoice(VoiceCreateData $request): Response
    {
        return $this->connector->send(new CreateVoiceRequest($request));
    }

    /**
     * Create a new voice and return typed DTO.
     */
    public function createVoiceDto(VoiceCreateData $request): VoiceResponse
    {
        return $this->createVoice($request)->dto();
    }

    /**
     * Get voice details (excluding sample).
     */
    public function getVoice(string $voiceId): Response
    {
        return $this->connector->send(new GetVoiceRequest($voiceId));
    }

    /**
     * Get voice details and return typed DTO.
     */
    public function getVoiceDto(string $voiceId): VoiceResponse
    {
        return $this->getVoice($voiceId)->dto();
    }

    /**
     * Update voice metadata (name, gender, languages, age, tags).
     */
    public function updateVoice(string $voiceId, VoiceUpdateData $request): Response
    {
        return $this->connector->send(new UpdateVoiceRequest($voiceId, $request));
    }

    /**
     * Update voice metadata and return typed DTO.
     */
    public function updateVoiceDto(string $voiceId, VoiceUpdateData $request): VoiceResponse
    {
        return $this->updateVoice($voiceId, $request)->dto();
    }

    /**
     * Delete a custom voice.
     */
    public function deleteVoice(string $voiceId): Response
    {
        return $this->connector->send(new DeleteVoiceRequest($voiceId));
    }

    /**
     * Delete a custom voice and return the typed DTO of the deleted voice.
     */
    public function deleteVoiceDto(string $voiceId): VoiceResponse
    {
        return $this->deleteVoice($voiceId)->dto();
    }

    /**
     * Get the audio sample for a voice. Returns raw `audio/wav` bytes via `$response->body()`.
     */
    public function getVoiceSample(string $voiceId): Response
    {
        return $this->connector->send(new GetVoiceSampleRequest($voiceId));
    }
}
