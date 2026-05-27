# Voice Management with Mistral PHP SDK

Create, list, fetch, update, and delete custom voices via `/v1/audio/voices`.

## What this example covers

- Creating a custom voice from a local audio file (uploads as base64)
- Listing voices with pagination
- Getting voice details
- Updating voice metadata (name, languages, tags, …)
- Downloading the original voice sample as raw WAV
- Deleting a voice

## Running

```bash
# Place a short, clean voice clip at ../shared/fixtures/voice.mp3 (or update the path)
php voices.php
```

## Quick reference

```php
use HelgeSverre\Mistral\Dto\Audio\VoiceCreateRequest;
use HelgeSverre\Mistral\Dto\Audio\VoiceUpdateRequest;

$voice = $mistral->audio()->createVoiceDto(
    VoiceCreateRequest::fromFile(
        name: 'Alice',
        filePath: '/path/to/sample.wav',
        languages: ['en'],
    )
);

$list = $mistral->audio()->listVoicesDto(limit: 20);

$mistral->audio()->updateVoice($voice->id, new VoiceUpdateRequest(name: 'Alice v2'));

$wavBytes = $mistral->audio()->getVoiceSample($voice->id)->body();

$mistral->audio()->deleteVoice($voice->id);
```

Use the created voice for TTS via `SpeechRequest::withVoice(voiceId: $voice->id, ...)` — see [`../11-speech`](../11-speech).
