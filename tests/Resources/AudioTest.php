<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Audio\TranscriptionResponse;
use HelgeSverre\Mistral\Dto\Audio\TranscriptionSegment;
use HelgeSverre\Mistral\Dto\Audio\TranscriptionWord;
use HelgeSverre\Mistral\Enums\ResponseFormat;
use HelgeSverre\Mistral\Enums\TimestampGranularity;
use HelgeSverre\Mistral\Requests\Audio\CreateTranscriptionRequest;
use HelgeSverre\Mistral\Requests\Audio\CreateTranscriptionStreamRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
});

it('can transcribe audio file', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription'),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'audio_');
    file_put_contents($tempFile, 'fake audio content');

    $response = $this->mistral->audio()->transcribe(
        filePath: $tempFile,
        model: 'whisper-large-v3'
    );

    Saloon::assertSent(CreateTranscriptionRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(TranscriptionResponse::class)
        ->and($dto->text)->toBe('Hello, this is a test transcription of an audio file.');

    unlink($tempFile);
});

it('can transcribe audio with language parameter', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription_with_language'),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'audio_');
    file_put_contents($tempFile, 'fake audio content');

    $response = $this->mistral->audio()->transcribe(
        filePath: $tempFile,
        model: 'whisper-large-v3',
        language: 'fr'
    );

    Saloon::assertSent(CreateTranscriptionRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(TranscriptionResponse::class)
        ->and($dto->text)->toBe("Bonjour, ceci est un test de transcription d'un fichier audio.")
        ->and($dto->language)->toBe('fr');

    unlink($tempFile);
});

it('can transcribe audio with verbose JSON format', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription_verbose'),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'audio_');
    file_put_contents($tempFile, 'fake audio content');

    $response = $this->mistral->audio()->transcribe(
        filePath: $tempFile,
        model: 'whisper-large-v3',
        responseFormat: ResponseFormat::VERBOSE_JSON
    );

    Saloon::assertSent(CreateTranscriptionRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(TranscriptionResponse::class)
        ->and($dto->text)->toBe('Hello, this is a test transcription of an audio file.')
        ->and($dto->language)->toBe('en')
        ->and($dto->duration)->toBe(5.2);

    unlink($tempFile);
});

it('can transcribe audio with word-level timestamps', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription_verbose'),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'audio_');
    file_put_contents($tempFile, 'fake audio content');

    $response = $this->mistral->audio()->transcribe(
        filePath: $tempFile,
        model: 'whisper-large-v3',
        responseFormat: ResponseFormat::VERBOSE_JSON,
        timestampGranularities: [TimestampGranularity::WORD]
    );

    Saloon::assertSent(CreateTranscriptionRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(TranscriptionResponse::class)
        ->and($dto->words)->toBeArray()
        ->and($dto->words)->toHaveCount(10)
        ->and($dto->words[0])->toBeInstanceOf(TranscriptionWord::class)
        ->and($dto->words[0]->word)->toBe('Hello')
        ->and($dto->words[0]->start)->toBe(0.0)
        ->and($dto->words[0]->end)->toBe(0.5);

    unlink($tempFile);
});

it('can transcribe audio with segment-level timestamps', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription_verbose'),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'audio_');
    file_put_contents($tempFile, 'fake audio content');

    $response = $this->mistral->audio()->transcribe(
        filePath: $tempFile,
        model: 'whisper-large-v3',
        responseFormat: ResponseFormat::VERBOSE_JSON,
        timestampGranularities: [TimestampGranularity::SEGMENT]
    );

    Saloon::assertSent(CreateTranscriptionRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(TranscriptionResponse::class)
        ->and($dto->segments)->toBeArray()
        ->and($dto->segments)->toHaveCount(1)
        ->and($dto->segments[0])->toBeInstanceOf(TranscriptionSegment::class)
        ->and($dto->segments[0]->id)->toBe(0)
        ->and($dto->segments[0]->text)->toBe('Hello, this is a test transcription of an audio file.')
        ->and($dto->segments[0]->start)->toBe(0.0)
        ->and($dto->segments[0]->end)->toBe(5.2)
        ->and($dto->segments[0]->temperature)->toBe(0.0)
        ->and($dto->segments[0]->avgLogprob)->toBe(-0.25)
        ->and($dto->segments[0]->compressionRatio)->toBe(1.5)
        ->and($dto->segments[0]->noSpeechProb)->toBe(0.01)
        ->and($dto->segments[0]->tokens)->toBeArray();

    unlink($tempFile);
});

it('can transcribe audio with both word and segment timestamps', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription_verbose'),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'audio_');
    file_put_contents($tempFile, 'fake audio content');

    $response = $this->mistral->audio()->transcribe(
        filePath: $tempFile,
        model: 'whisper-large-v3',
        responseFormat: ResponseFormat::VERBOSE_JSON,
        timestampGranularities: [
            TimestampGranularity::WORD,
            TimestampGranularity::SEGMENT,
        ]
    );

    Saloon::assertSent(CreateTranscriptionRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(TranscriptionResponse::class)
        ->and($dto->words)->toBeArray()
        ->and($dto->words)->toHaveCount(10)
        ->and($dto->segments)->toBeArray()
        ->and($dto->segments)->toHaveCount(1);

    unlink($tempFile);
});

it('can transcribe audio with temperature parameter', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription'),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'audio_');
    file_put_contents($tempFile, 'fake audio content');

    $response = $this->mistral->audio()->transcribe(
        filePath: $tempFile,
        model: 'whisper-large-v3',
        temperature: 0.5
    );

    Saloon::assertSent(CreateTranscriptionRequest::class);

    expect($response->status())->toBe(200);

    unlink($tempFile);
});

it('can transcribe audio with prompt parameter', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription'),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'audio_');
    file_put_contents($tempFile, 'fake audio content');

    $response = $this->mistral->audio()->transcribe(
        filePath: $tempFile,
        model: 'whisper-large-v3',
        prompt: 'This is a technical discussion about AI.'
    );

    Saloon::assertSent(CreateTranscriptionRequest::class);

    expect($response->status())->toBe(200);

    unlink($tempFile);
});

it('can transcribe audio with all parameters', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription_verbose'),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'audio_');
    file_put_contents($tempFile, 'fake audio content');

    $response = $this->mistral->audio()->transcribe(
        filePath: $tempFile,
        model: 'whisper-large-v3',
        language: 'en',
        prompt: 'Technical discussion',
        responseFormat: ResponseFormat::VERBOSE_JSON,
        temperature: 0.3,
        timestampGranularities: [
            TimestampGranularity::WORD,
            TimestampGranularity::SEGMENT,
        ]
    );

    Saloon::assertSent(CreateTranscriptionRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(TranscriptionResponse::class)
        ->and($dto->text)->toBeString()
        ->and($dto->language)->toBe('en')
        ->and($dto->duration)->toBeFloat()
        ->and($dto->words)->toBeArray()
        ->and($dto->segments)->toBeArray();

    unlink($tempFile);
});

it('can transcribe audio with streaming', function () {
    Saloon::fake([
        CreateTranscriptionStreamRequest::class => MockResponse::make(
            body: 'data: '.json_encode(['text' => 'Hello, '])."\n\n".
                  'data: '.json_encode(['text' => 'world!'])."\n\n".
                  "data: [DONE]\n\n",
            status: 200,
            headers: ['Content-Type' => 'text/event-stream']
        ),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'audio_');
    file_put_contents($tempFile, 'fake audio content');

    $stream = $this->mistral->audio()->transcribeStreamed(
        filePath: $tempFile,
        model: 'whisper-large-v3'
    );

    $chunks = iterator_to_array($stream);

    Saloon::assertSent(CreateTranscriptionStreamRequest::class);

    expect($chunks)->toHaveCount(2)
        ->and($chunks[0]['text'])->toBe('Hello, ')
        ->and($chunks[1]['text'])->toBe('world!');

    unlink($tempFile);
});

it('ResponseFormat enum has correct values', function () {
    expect(ResponseFormat::JSON->value)->toBe('json')
        ->and(ResponseFormat::TEXT->value)->toBe('text')
        ->and(ResponseFormat::VERBOSE_JSON->value)->toBe('verbose_json');
});

it('TimestampGranularity enum has correct values', function () {
    expect(TimestampGranularity::WORD->value)->toBe('word')
        ->and(TimestampGranularity::SEGMENT->value)->toBe('segment');
});
