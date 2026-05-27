<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Audio\SpeechRequest;
use HelgeSverre\Mistral\Dto\Audio\SpeechResponse;
use HelgeSverre\Mistral\Dto\Audio\SpeechStreamAudioDelta;
use HelgeSverre\Mistral\Dto\Audio\SpeechStreamDone;
use HelgeSverre\Mistral\Dto\Audio\TranscriptionResponse;
use HelgeSverre\Mistral\Dto\Audio\TranscriptionSegment;
use HelgeSverre\Mistral\Dto\Audio\TranscriptionWord;
use HelgeSverre\Mistral\Dto\Audio\VoiceCreateRequest as VoiceCreateData;
use HelgeSverre\Mistral\Dto\Audio\VoiceListResponse;
use HelgeSverre\Mistral\Dto\Audio\VoiceResponse;
use HelgeSverre\Mistral\Dto\Audio\VoiceUpdateRequest as VoiceUpdateData;
use HelgeSverre\Mistral\Enums\ResponseFormat;
use HelgeSverre\Mistral\Enums\SpeechOutputFormat;
use HelgeSverre\Mistral\Enums\TimestampGranularity;
use HelgeSverre\Mistral\Mistral;
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
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Spatie\LaravelData\DataCollection;

beforeEach(function () {
    $this->mistral = new Mistral(apiKey: config('mistral.api_key'));
});

it('can transcribe audio file', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription'),
    ]);

    $audioFile = __DIR__.'/../Fixtures/test-data/test-audio.mp3';

    $response = $this->mistral->audio()->transcribe(
        filePath: $audioFile,
        model: 'voxtral-mini-latest'
    );

    Saloon::assertSent(CreateTranscriptionRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(TranscriptionResponse::class)
        ->and($dto->text)->toBe('Hello, this is a test transcription of an audio file.');
});

it('can transcribe audio with language parameter', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription_with_language'),
    ]);

    $audioFile = __DIR__.'/../Fixtures/test-data/test-audio.mp3';

    $response = $this->mistral->audio()->transcribe(
        filePath: $audioFile,
        model: 'voxtral-mini-latest',
        language: 'fr'
    );

    Saloon::assertSent(CreateTranscriptionRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(TranscriptionResponse::class)
        ->and($dto->text)->toBe("Bonjour, ceci est un test de transcription d'un fichier audio.")
        ->and($dto->language)->toBe('fr');
});

it('can transcribe audio with verbose JSON format', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription_verbose'),
    ]);

    $audioFile = __DIR__.'/../Fixtures/test-data/test-audio.mp3';

    $response = $this->mistral->audio()->transcribe(
        filePath: $audioFile,
        model: 'voxtral-mini-latest',
        responseFormat: ResponseFormat::VERBOSE_JSON
    );

    Saloon::assertSent(CreateTranscriptionRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(TranscriptionResponse::class)
        ->and($dto->text)->toBe('Hello, this is a test transcription of an audio file.')
        ->and($dto->language)->toBe('en')
        ->and($dto->duration)->toBe(5.2);
});

it('can transcribe audio with word-level timestamps', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription_verbose'),
    ]);

    $audioFile = __DIR__.'/../Fixtures/test-data/test-audio.mp3';

    $response = $this->mistral->audio()->transcribe(
        filePath: $audioFile,
        model: 'voxtral-mini-latest',
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
});

it('can transcribe audio with segment-level timestamps', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription_verbose'),
    ]);

    $audioFile = __DIR__.'/../Fixtures/test-data/test-audio.mp3';

    $response = $this->mistral->audio()->transcribe(
        filePath: $audioFile,
        model: 'voxtral-mini-latest',
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
});

it('can transcribe audio with both word and segment timestamps', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription_verbose'),
    ]);

    $audioFile = __DIR__.'/../Fixtures/test-data/test-audio.mp3';

    $response = $this->mistral->audio()->transcribe(
        filePath: $audioFile,
        model: 'voxtral-mini-latest',
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
});

it('can transcribe audio with temperature parameter', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription'),
    ]);

    $audioFile = __DIR__.'/../Fixtures/test-data/test-audio.mp3';

    $response = $this->mistral->audio()->transcribe(
        filePath: $audioFile,
        model: 'voxtral-mini-latest',
        temperature: 0.5
    );

    Saloon::assertSent(CreateTranscriptionRequest::class);

    expect($response->status())->toBe(200);
});

it('can transcribe audio with prompt parameter', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription'),
    ]);

    $audioFile = __DIR__.'/../Fixtures/test-data/test-audio.mp3';

    $response = $this->mistral->audio()->transcribe(
        filePath: $audioFile,
        model: 'voxtral-mini-latest',
        prompt: 'This is a technical discussion about AI.'
    );

    Saloon::assertSent(CreateTranscriptionRequest::class);

    expect($response->status())->toBe(200);
});

it('can transcribe audio with all parameters', function () {
    Saloon::fake([
        CreateTranscriptionRequest::class => MockResponse::fixture('audio/transcription_verbose'),
    ]);

    $audioFile = __DIR__.'/../Fixtures/test-data/test-audio.mp3';

    $response = $this->mistral->audio()->transcribe(
        filePath: $audioFile,
        model: 'voxtral-mini-latest',
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

    $audioFile = __DIR__.'/../Fixtures/test-data/test-audio.mp3';

    $stream = $this->mistral->audio()->transcribeStreamed(
        filePath: $audioFile,
        model: 'voxtral-mini-latest'
    );

    $chunks = iterator_to_array($stream);

    Saloon::assertSent(CreateTranscriptionStreamRequest::class);

    expect($chunks)->toHaveCount(2)
        ->and($chunks[0]['text'])->toBe('Hello, ')
        ->and($chunks[1]['text'])->toBe('world!');
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

it('SpeechOutputFormat enum has correct values', function () {
    expect(SpeechOutputFormat::PCM->value)->toBe('pcm')
        ->and(SpeechOutputFormat::WAV->value)->toBe('wav')
        ->and(SpeechOutputFormat::MP3->value)->toBe('mp3')
        ->and(SpeechOutputFormat::FLAC->value)->toBe('flac')
        ->and(SpeechOutputFormat::OPUS->value)->toBe('opus');
});

it('can generate speech with a preset voice', function () {
    Saloon::fake([
        CreateSpeechRequest::class => MockResponse::fixture('audio/speech'),
    ]);

    $response = $this->mistral->audio()->speech(
        SpeechRequest::withVoice(
            input: 'Hello, world!',
            voiceId: 'alice',
            model: 'voxtral-tts-latest',
            responseFormat: SpeechOutputFormat::MP3,
        )
    );

    Saloon::assertSent(CreateSpeechRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(SpeechResponse::class)
        ->and($dto->audioData)->toBe('SGVsbG8sIHdvcmxkIQ==')
        ->and($dto->decoded())->toBe('Hello, world!');
});

it('can generate speech and return typed DTO', function () {
    Saloon::fake([
        CreateSpeechRequest::class => MockResponse::fixture('audio/speech'),
    ]);

    $dto = $this->mistral->audio()->speechDto(
        SpeechRequest::withVoice(input: 'Hi', voiceId: 'alice')
    );

    expect($dto)->toBeInstanceOf(SpeechResponse::class)
        ->and($dto->audioData)->toBe('SGVsbG8sIHdvcmxkIQ==');
});

it('can stream generated speech', function () {
    $delta = json_encode(['type' => 'speech.audio.delta', 'audio_data' => 'AAAA']);
    $done = json_encode([
        'type' => 'speech.audio.done',
        'usage' => ['prompt_tokens' => 5, 'completion_tokens' => 0, 'total_tokens' => 5],
    ]);

    Saloon::fake([
        CreateSpeechStreamRequest::class => MockResponse::make(
            body: "data: {$delta}\n\ndata: {$done}\n\ndata: [DONE]\n\n",
            status: 200,
            headers: ['Content-Type' => 'text/event-stream']
        ),
    ]);

    $events = iterator_to_array(
        $this->mistral->audio()->speechStreamed(
            SpeechRequest::withVoice(input: 'Hello', voiceId: 'alice')
        ),
        preserve_keys: false,
    );

    Saloon::assertSent(CreateSpeechStreamRequest::class);

    expect($events)->toHaveCount(2)
        ->and($events[0])->toBeInstanceOf(SpeechStreamAudioDelta::class)
        ->and($events[0]->audioData)->toBe('AAAA')
        ->and($events[1])->toBeInstanceOf(SpeechStreamDone::class)
        ->and($events[1]->usage->totalTokens)->toBe(5);
});

it('can list voices', function () {
    Saloon::fake([
        ListVoicesRequest::class => MockResponse::fixture('audio/voices_list'),
    ]);

    $response = $this->mistral->audio()->listVoices(limit: 10);

    Saloon::assertSent(ListVoicesRequest::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(VoiceListResponse::class)
        ->and($dto->total)->toBe(2)
        ->and($dto->page)->toBe(1)
        ->and($dto->pageSize)->toBe(10)
        ->and($dto->totalPages)->toBe(1)
        ->and($dto->items)->toBeInstanceOf(DataCollection::class)
        ->and($dto->items[0])->toBeInstanceOf(VoiceResponse::class)
        ->and($dto->items[0]->name)->toBe('Alice')
        ->and($dto->items[0]->languages)->toBe(['en', 'fr']);
});

it('can create a voice from a base64 audio sample', function () {
    Saloon::fake([
        CreateVoiceRequest::class => MockResponse::fixture('audio/voices_create'),
    ]);

    $response = $this->mistral->audio()->createVoice(
        new VoiceCreateData(
            name: 'Alice',
            sampleAudio: base64_encode('FAKE_AUDIO_BYTES'),
            slug: 'alice',
            languages: ['en'],
            gender: 'female',
            age: 30,
            tags: ['warm'],
        )
    );

    Saloon::assertSent(CreateVoiceRequest::class);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(VoiceResponse::class)
        ->and($dto->id)->toBe('01234567-89ab-cdef-0123-456789abcdef')
        ->and($dto->name)->toBe('Alice');
});

it('can create a voice from a file via fromFile helper', function () {
    Saloon::fake([
        CreateVoiceRequest::class => MockResponse::fixture('audio/voices_create'),
    ]);

    $audioFile = __DIR__.'/../Fixtures/test-data/test-audio.mp3';

    $dto = $this->mistral->audio()->createVoiceDto(
        VoiceCreateData::fromFile(
            name: 'Alice',
            filePath: $audioFile,
            slug: 'alice',
            languages: ['en'],
        )
    );

    Saloon::assertSent(CreateVoiceRequest::class);
    expect($dto)->toBeInstanceOf(VoiceResponse::class);
});

it('can get a voice', function () {
    Saloon::fake([
        GetVoiceRequest::class => MockResponse::fixture('audio/voices_get'),
    ]);

    $dto = $this->mistral->audio()->getVoiceDto('01234567-89ab-cdef-0123-456789abcdef');

    Saloon::assertSent(GetVoiceRequest::class);
    expect($dto)->toBeInstanceOf(VoiceResponse::class)
        ->and($dto->name)->toBe('Alice')
        ->and($dto->tags)->toBe(['warm', 'professional']);
});

it('can update voice metadata', function () {
    Saloon::fake([
        UpdateVoiceRequest::class => MockResponse::fixture('audio/voices_update'),
    ]);

    $dto = $this->mistral->audio()->updateVoiceDto(
        '01234567-89ab-cdef-0123-456789abcdef',
        new VoiceUpdateData(
            name: 'Alice Updated',
            languages: ['en', 'es'],
            age: 31,
            tags: ['warm', 'narrator'],
        )
    );

    Saloon::assertSent(UpdateVoiceRequest::class);
    expect($dto)->toBeInstanceOf(VoiceResponse::class)
        ->and($dto->name)->toBe('Alice Updated')
        ->and($dto->languages)->toBe(['en', 'es'])
        ->and($dto->age)->toBe(31);
});

it('can delete a voice', function () {
    Saloon::fake([
        DeleteVoiceRequest::class => MockResponse::fixture('audio/voices_delete'),
    ]);

    $dto = $this->mistral->audio()->deleteVoiceDto('01234567-89ab-cdef-0123-456789abcdef');

    Saloon::assertSent(DeleteVoiceRequest::class);
    expect($dto)->toBeInstanceOf(VoiceResponse::class)
        ->and($dto->id)->toBe('01234567-89ab-cdef-0123-456789abcdef');
});

it('can fetch a voice audio sample', function () {
    Saloon::fake([
        GetVoiceSampleRequest::class => MockResponse::fixture('audio/voices_sample'),
    ]);

    $response = $this->mistral->audio()->getVoiceSample('01234567-89ab-cdef-0123-456789abcdef');

    Saloon::assertSent(GetVoiceSampleRequest::class);
    expect($response->status())->toBe(200)
        ->and($response->body())->toBe('FAKE-WAV-BYTES');
});
