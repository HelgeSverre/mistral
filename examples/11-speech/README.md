# Text-to-Speech with Mistral PHP SDK

Generate spoken audio from text using Mistral's `/v1/audio/speech` endpoint.

## What this example covers

- Generating an MP3 from text with a preset voice
- Picking a different output format (`mp3`, `wav`, `opus`, `flac`, `pcm`)
- Streaming audio chunks back as they are produced

## Running

```bash
cp .env.example ../.env   # if you haven't set MISTRAL_API_KEY yet
php speech.php
```

The example writes audio files (`output-hello.mp3`, `output-format.*`, `output-streamed.mp3`) into this directory.

## Quick reference

```php
use HelgeSverre\Mistral\Dto\Audio\SpeechRequest;
use HelgeSverre\Mistral\Enums\SpeechOutputFormat;

// Preset voice
$dto = $mistral->audio()->speechDto(
    SpeechRequest::withVoice(
        input: 'Hello!',
        voiceId: 'alice',
        responseFormat: SpeechOutputFormat::MP3,
    )
);
$dto->saveTo('hello.mp3');

// Zero-shot voice cloning from a reference clip
$dto = $mistral->audio()->speechDto(
    SpeechRequest::withRefAudioFile(
        input: 'Hello!',
        filePath: '/path/to/reference.wav',
    )
);
```

## Streaming

`speechStreamed()` yields `SpeechStreamAudioDelta` events with base64-encoded chunks, followed by a single `SpeechStreamDone` with usage info.
