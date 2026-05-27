<?php

/**
 * Text-to-Speech (TTS)
 *
 * Description: Generate spoken audio from text using Mistral's audio speech API.
 * Use Case: Voice assistants, accessibility, audio summaries, narration.
 * Prerequisites: MISTRAL_API_KEY in .env file. Optional: a custom voice id.
 *
 * @see https://docs.mistral.ai/capabilities/audio/
 */

declare(strict_types=1);

require_once __DIR__.'/../shared/bootstrap.php';

use HelgeSverre\Mistral\Dto\Audio\SpeechRequest;
use HelgeSverre\Mistral\Dto\Audio\SpeechStreamAudioDelta;
use HelgeSverre\Mistral\Dto\Audio\SpeechStreamDone;
use HelgeSverre\Mistral\Enums\SpeechOutputFormat;
use HelgeSverre\Mistral\Mistral;

function main(): void
{
    displayTitle('Text-to-Speech', '🔊');

    $mistral = createMistralClient();

    try {
        basicSpeech($mistral);
        speechFormats($mistral);
        streamingSpeech($mistral);
    } catch (Throwable $e) {
        handleError($e);
    }
}

function basicSpeech(Mistral $mistral): void
{
    displaySection('Example 1: Generate speech with a preset voice');

    $output = __DIR__.'/output-hello.mp3';

    $dto = $mistral->audio()->speechDto(
        SpeechRequest::withVoice(
            input: 'Hello! This is the Mistral text-to-speech API speaking from PHP.',
            voiceId: 'alice',
            model: 'voxtral-tts-latest',
            responseFormat: SpeechOutputFormat::MP3,
        )
    );

    $dto->saveTo($output);

    echo "Saved {$output} (".formatBytes(strlen($dto->decoded())).")\n\n";
}

function speechFormats(Mistral $mistral): void
{
    displaySection('Example 2: Different output formats');

    foreach ([SpeechOutputFormat::MP3, SpeechOutputFormat::WAV, SpeechOutputFormat::OPUS] as $format) {
        $output = __DIR__.'/output-format.'.$format->value;

        $dto = $mistral->audio()->speechDto(
            SpeechRequest::withVoice(
                input: 'Format demo.',
                voiceId: 'alice',
                responseFormat: $format,
            )
        );

        $dto->saveTo($output);

        echo "  {$format->value}: ".formatBytes(strlen($dto->decoded()))."\n";
    }
    echo "\n";
}

function streamingSpeech(Mistral $mistral): void
{
    displaySection('Example 3: Stream audio chunks as they are generated');

    $output = __DIR__.'/output-streamed.mp3';
    $fh = fopen($output, 'wb');

    foreach ($mistral->audio()->speechStreamed(
        SpeechRequest::withVoice(
            input: 'Streaming lets you start playing audio before the whole clip is ready.',
            voiceId: 'alice',
            responseFormat: SpeechOutputFormat::MP3,
        )
    ) as $event) {
        if ($event instanceof SpeechStreamAudioDelta) {
            fwrite($fh, $event->decoded());
        } elseif ($event instanceof SpeechStreamDone) {
            echo "  Done. Tokens used: {$event->usage->totalTokens}\n";
        }
    }

    fclose($fh);

    echo "  Saved {$output} (".formatBytes(filesize($output)).")\n\n";
}

main();
