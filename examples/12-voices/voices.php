<?php

/**
 * Voice Management
 *
 * Description: Create, list, update, fetch, and delete custom voices via the audio voices API.
 * Use Case: Branded TTS voices, voice cloning, voice libraries.
 * Prerequisites: MISTRAL_API_KEY in .env file, a sample audio file for voice creation.
 *
 * @see https://docs.mistral.ai/capabilities/audio/
 */

declare(strict_types=1);

require_once __DIR__.'/../shared/bootstrap.php';

use HelgeSverre\Mistral\Dto\Audio\VoiceCreateRequest;
use HelgeSverre\Mistral\Dto\Audio\VoiceUpdateRequest;
use HelgeSverre\Mistral\Mistral;

function main(): void
{
    displayTitle('Voices', '🗣');

    $mistral = createMistralClient();

    try {
        $voiceId = createCustomVoice($mistral);
        listVoices($mistral);

        if ($voiceId !== null) {
            getVoice($mistral, $voiceId);
            updateVoice($mistral, $voiceId);
            fetchSample($mistral, $voiceId);
            deleteVoice($mistral, $voiceId);
        }
    } catch (Throwable $e) {
        handleError($e);
    }
}

function createCustomVoice(Mistral $mistral): ?string
{
    displaySection('Example 1: Create a custom voice from a sample file');

    $sampleFile = __DIR__.'/../shared/fixtures/voice.mp3';

    if (! file_exists($sampleFile)) {
        echo "⚠️  Sample audio file not found: {$sampleFile}\n";
        echo "ℹ️  Place a short, clean voice recording at the path above and rerun.\n\n";

        return null;
    }

    $voice = $mistral->audio()->createVoiceDto(
        VoiceCreateRequest::fromFile(
            name: 'My PHP Voice',
            filePath: $sampleFile,
            slug: 'my-php-voice',
            languages: ['en'],
            gender: 'neutral',
            tags: ['demo'],
        )
    );

    echo "Created voice id: {$voice->id}\n\n";

    return $voice->id;
}

function listVoices(Mistral $mistral): void
{
    displaySection('Example 2: List voices');

    $list = $mistral->audio()->listVoicesDto(limit: 10);

    echo "Page {$list->page}/{$list->totalPages} ({$list->total} total)\n";
    foreach ($list->items as $voice) {
        $languages = implode(',', $voice->languages);
        echo "  - {$voice->name} [{$voice->id}] languages=[{$languages}]\n";
    }
    echo "\n";
}

function getVoice(Mistral $mistral, string $voiceId): void
{
    displaySection('Example 3: Get a voice by id');

    $voice = $mistral->audio()->getVoiceDto($voiceId);
    echo "Name: {$voice->name}\n";
    echo 'Languages: '.implode(', ', $voice->languages)."\n\n";
}

function updateVoice(Mistral $mistral, string $voiceId): void
{
    displaySection('Example 4: Update voice metadata');

    $voice = $mistral->audio()->updateVoiceDto(
        $voiceId,
        new VoiceUpdateRequest(
            name: 'My PHP Voice (updated)',
            tags: ['demo', 'updated'],
        )
    );

    echo "Updated name: {$voice->name}\n";
    echo 'Updated tags: '.implode(', ', $voice->tags ?? [])."\n\n";
}

function fetchSample(Mistral $mistral, string $voiceId): void
{
    displaySection('Example 5: Download the voice sample');

    $response = $mistral->audio()->getVoiceSample($voiceId);
    $output = __DIR__.'/sample-'.$voiceId.'.wav';

    file_put_contents($output, $response->body());
    echo "Saved {$output} (".formatBytes(filesize($output)).")\n\n";
}

function deleteVoice(Mistral $mistral, string $voiceId): void
{
    displaySection('Example 6: Delete the voice');

    $mistral->audio()->deleteVoice($voiceId);
    echo "Deleted voice {$voiceId}\n\n";
}

main();
