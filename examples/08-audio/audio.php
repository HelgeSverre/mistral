<?php

/**
 * Audio Transcription
 *
 * Description: Transcribe audio files to text using Mistral's audio models
 * Use Case: Speech-to-text, meeting transcription, subtitle generation, voice notes
 * Prerequisites: MISTRAL_API_KEY in .env file, audio file
 *
 * @see https://docs.mistral.ai/capabilities/audio/
 */

declare(strict_types=1);

require_once __DIR__.'/../shared/bootstrap.php';

use HelgeSverre\Mistral\Enums\ResponseFormat;
use HelgeSverre\Mistral\Enums\TimestampGranularity;
use HelgeSverre\Mistral\Mistral;

/**
 * Main execution function
 */
function main(): void
{
    displayTitle('Audio Transcription', '🎤');

    $mistral = createMistralClient();

    try {
        // Example 1: Basic audio transcription
        basicTranscription($mistral);

        // Example 2: Transcription with timestamps
        transcriptionWithTimestamps($mistral);

        // Example 3: Different response formats
        responseFormats($mistral);

        // Example 4: Streaming transcription
        streamingTranscription($mistral);

    } catch (Throwable $e) {
        handleError($e);
    }
}

/**
 * Example 1: Basic audio transcription
 */
function basicTranscription(Mistral $mistral): void
{
    displaySection('Example 1: Basic Transcription');
    echo "Transcribing an audio file to text...\n\n";

    // For this example, you need an actual audio file
    // Supported formats: MP3, MP4, MPEG, MPGA, M4A, WAV, WEBM
    // Replace with your audio file path
    $audioFile = __DIR__.'/../shared/fixtures/voice.mp3';

    // Check if file exists, otherwise skip this example
    if (! file_exists($audioFile)) {
        echo "⚠️ Sample audio file not found: {$audioFile}\n";
        echo "ℹ️ To run this example:\n";
        echo "  1. Place an audio file at: {$audioFile}\n";
        echo "  2. Or update the \$audioFile path to your audio file\n\n";

        echo "📝 Example code structure:\n";
        echo str_repeat('─', 60)."\n";
        echo "```php\n";
        echo "\$response = \$mistral->audio()->transcribe(\n";
        echo "    filePath: '/path/to/audio.mp3',\n";
        echo "    model: 'voxtral-mini-latest', // Mistral's audio model\n";
        echo "    language: 'en', // Optional: ISO-639-1 language code\n";
        echo "    responseFormat: ResponseFormat::JSON, // Output format\n";
        echo ");\n\n";
        echo "\$dto = \$response->dto();\n";
        echo "echo \"Transcription: {\$dto->getText()}\\n\";\n";
        echo "```\n";
        echo str_repeat('─', 60)."\n\n";

        echo "💡 Audio File Guidelines:\n";
        echo "  • Supported formats: MP3, WAV, M4A, MP4, WEBM\n";
        echo "  • Max file size: Check current API limits\n";
        echo "  • Best quality: 16kHz+ sample rate\n";
        echo "  • Clear audio produces better transcriptions\n";
        echo "  • Background noise affects accuracy\n\n";

        return;
    }

    echo "📁 Audio file: {$audioFile}\n";
    echo '📊 File size: '.formatBytes(filesize($audioFile))."\n";
    echo "🔄 Starting transcription...\n\n";

    // Transcribe the audio file
    $response = measureTime(
        callback: fn () => $mistral->audio()->transcribe(
            filePath: $audioFile,
            model: 'voxtral-mini-latest',
            language: 'en', // Optional: specify language for better accuracy
            responseFormat: ResponseFormat::JSON,
        ),
        label: 'Audio Transcription',
    );

    $dto = $response->dtoOrFail();

    echo "✅ Transcription completed\n\n";

    echo "📝 Transcription Result:\n";
    echo str_repeat('─', 60)."\n";
    echo $dto->getText()."\n";
    echo str_repeat('─', 60)."\n\n";

    echo "📊 Metadata:\n";
    echo '  • Language detected: '.($dto->language ?? 'N/A')."\n";
    echo '  • Duration: '.($dto->duration ?? 'N/A')." seconds\n";
    echo '  • Segments: '.count($dto->segments ?? [])."\n\n";

    echo "💡 Basic Transcription Use Cases:\n";
    echo "  • Meeting notes and summaries\n";
    echo "  • Voice memo transcription\n";
    echo "  • Podcast/video subtitles\n";
    echo "  • Call center transcripts\n";
    echo "  • Interview documentation\n\n";
}

/**
 * Example 2: Transcription with timestamps
 */
function transcriptionWithTimestamps(Mistral $mistral): void
{
    displaySection('Example 2: Timestamps');
    echo "Transcribing with word-level and segment-level timestamps...\n\n";

    $audioFile = __DIR__.'/../shared/fixtures/voice.mp3';

    if (! file_exists($audioFile)) {
        echo "⚠️ Audio file not found: {$audioFile}\n";
        echo "📝 Example with timestamps:\n\n";
        echo "```php\n";
        echo "\$response = \$mistral->audio()->transcribe(\n";
        echo "    filePath: \$audioFile,\n";
        echo "    model: 'voxtral-mini-latest',\n";
        echo "    responseFormat: ResponseFormat::VERBOSE_JSON,\n";
        echo "    timestampGranularities: [\n";
        echo "        TimestampGranularity::WORD,    // Word-level timestamps\n";
        echo "        TimestampGranularity::SEGMENT, // Segment-level timestamps\n";
        echo "    ],\n";
        echo ");\n";
        echo "```\n\n";

        echo "💡 Timestamp Granularities:\n";
        echo "  • WORD: Timestamp for each word (precise)\n";
        echo "  • SEGMENT: Timestamp for each phrase/sentence\n";
        echo "  • Use both for maximum detail\n\n";

        echo "💡 Timestamp Use Cases:\n";
        echo "  • Video subtitle generation (SRT, VTT)\n";
        echo "  • Searchable transcripts (jump to moment)\n";
        echo "  • Speaker timing analysis\n";
        echo "  • Audio editing assistance\n";
        echo "  • Synchronized translations\n\n";

        return;
    }

    echo "📁 Audio file: {$audioFile}\n";
    echo "🔄 Transcribing with detailed timestamps...\n\n";

    $response = $mistral->audio()->transcribe(
        filePath: $audioFile,
        model: 'voxtral-mini-latest',
        responseFormat: ResponseFormat::VERBOSE_JSON,
        timestampGranularities: [
            TimestampGranularity::WORD,
            TimestampGranularity::SEGMENT,
        ],
    );

    $dto = $response->dtoOrFail();

    echo "✅ Transcription with timestamps completed\n\n";

    // Display segments with timestamps
    if ($dto->segments !== null && count($dto->segments) > 0) {
        echo "🕐 Transcription Segments:\n";
        echo str_repeat('─', 60)."\n\n";

        // Segments is an array of TranscriptionSegment objects
        foreach (array_slice($dto->segments, 0, 5) as $i => $segment) {
            // Access object properties, not array keys
            $start = gmdate('H:i:s', (int) $segment->start);
            $end = gmdate('H:i:s', (int) $segment->end);

            echo 'Segment '.($i + 1).": [{$start} → {$end}]\n";
            echo "\"{$segment->text}\"\n\n";
        }

        if (count($dto->segments) > 5) {
            echo '... ('.count($dto->segments)." total segments)\n\n";
        }
    }

    // Display word-level timestamps if available
    if ($dto->words !== null && count($dto->words) > 0) {
        echo "📝 Word-level Timestamps (first 10 words):\n";
        echo str_repeat('─', 60)."\n";

        // Words is an array of TranscriptionWord objects
        foreach (array_slice($dto->words, 0, 10) as $word) {
            // Access object properties, not array keys
            $start = number_format($word->start, 2);
            $end = number_format($word->end, 2);
            echo "[{$start}s] {$word->word} ";
        }
        echo "\n\n";
    }
}

/**
 * Example 3: Different response formats
 */
function responseFormats(Mistral $mistral): void
{
    displaySection('Example 3: Response Formats');
    echo "Exploring different transcription output formats...\n\n";

    $audioFile = __DIR__.'/../shared/fixtures/voice.mp3';

    if (! file_exists($audioFile)) {
        echo "⚠️ Audio file not found\n\n";

        echo "📋 Available Response Formats:\n";
        echo str_repeat('─', 60)."\n\n";

        echo "1. JSON (default):\n";
        echo "   • Simple text output\n";
        echo "   • Best for basic transcription\n";
        echo "   • Returns: {\"text\": \"...\"}\n\n";

        echo "2. TEXT:\n";
        echo "   • Plain text only\n";
        echo "   • No JSON wrapper\n";
        echo "   • Direct string output\n\n";

        echo "3. VERBOSE_JSON:\n";
        echo "   • Detailed metadata\n";
        echo "   • Segments with timestamps\n";
        echo "   • Word-level timing\n";
        echo "   • Language detection\n";
        echo "   • Confidence scores\n\n";

        echo "4. SRT (SubRip):\n";
        echo "   • Subtitle format\n";
        echo "   • Numbered segments\n";
        echo "   • Timecodes\n";
        echo "   • Ready for video players\n\n";

        echo "5. VTT (WebVTT):\n";
        echo "   • Web-friendly subtitles\n";
        echo "   • HTML5 video compatible\n";
        echo "   • Styling support\n\n";

        echo "💡 Format Selection:\n";
        echo "  • Use JSON for most applications\n";
        echo "  • Use TEXT for simple display\n";
        echo "  • Use VERBOSE_JSON for detailed analysis\n";
        echo "  • Use SRT/VTT for video subtitles\n\n";

        return;
    }

    echo "📁 Audio file: {$audioFile}\n\n";

    $formats = [
        ['format' => ResponseFormat::JSON, 'label' => 'JSON (default)'],
        ['format' => ResponseFormat::TEXT, 'label' => 'Plain Text'],
        ['format' => ResponseFormat::VERBOSE_JSON, 'label' => 'Verbose JSON'],
    ];

    foreach ($formats as $item) {
        echo "Format: {$item['label']}\n";
        echo str_repeat('─', 40)."\n";

        $response = $mistral->audio()->transcribe(
            filePath: $audioFile,
            model: 'voxtral-mini-latest',
            responseFormat: $item['format'],
        );

        // Display based on format
        if ($item['format'] === ResponseFormat::TEXT) {
            echo 'Output: '.$response->body()."\n\n";
        } else {
            $dto = $response->dtoOrFail();
            $text = $dto->getText();
            if ($text !== null) {
                echo "Text: {$text}\n";
            }
            if ($dto->segments !== null) {
                echo 'Segments: '.count($dto->segments)."\n";
            }
            echo "\n";
        }
    }
}

/**
 * Example 4: Streaming transcription
 */
function streamingTranscription(Mistral $mistral): void
{
    displaySection('Example 4: Streaming Transcription');
    echo "Receiving transcription results in real-time...\n\n";

    $audioFile = __DIR__.'/../shared/fixtures/voice.mp3';

    if (! file_exists($audioFile)) {
        echo "⚠️ Audio file not found\n\n";

        echo "📝 Streaming Example:\n";
        echo str_repeat('─', 60)."\n";
        echo "```php\n";
        echo "\$stream = \$mistral->audio()->transcribeStreamed(\n";
        echo "    filePath: \$audioFile,\n";
        echo "    model: 'voxtral-mini-latest',\n";
        echo "    responseFormat: ResponseFormat::JSON,\n";
        echo ");\n\n";
        echo "foreach (\$stream as \$chunk) {\n";
        echo "    // Process each chunk as it arrives\n";
        echo "    echo \$chunk['text'];\n";
        echo "    flush();\n";
        echo "}\n";
        echo "```\n";
        echo str_repeat('─', 60)."\n\n";

        echo "💡 Streaming Benefits:\n";
        echo "  • See results as they're processed\n";
        echo "  • Better UX for long audio files\n";
        echo "  • Early feedback on transcription\n";
        echo "  • Handle large files incrementally\n\n";

        echo "⚠️ Streaming Considerations:\n";
        echo "  • May not be available for all models\n";
        echo "  • Network stability important\n";
        echo "  • Buffer management needed\n";
        echo "  • Error handling more complex\n\n";

        return;
    }

    echo "📁 Audio file: {$audioFile}\n";
    echo "🌊 Starting streaming transcription...\n\n";

    echo "Transcription:\n";
    echo str_repeat('─', 60)."\n";

    try {
        $stream = $mistral->audio()->transcribeStreamed(
            filePath: $audioFile,
            model: 'voxtral-mini-latest',
            responseFormat: ResponseFormat::JSON,
        );

        $chunkCount = 0;
        foreach ($stream as $chunk) {
            // Process each streaming chunk
            if (isset($chunk['text'])) {
                echo $chunk['text'];
                flush();
            }
            $chunkCount++;
        }

        echo "\n".str_repeat('─', 60)."\n";
        echo "✅ Streaming completed\n";
        echo "📊 Received {$chunkCount} chunks\n\n";

    } catch (Throwable $e) {
        echo "\n❌ Streaming error: {$e->getMessage()}\n\n";
    }

    echo "💡 Production Tips:\n";
    echo "  • Implement retry logic for failures\n";
    echo "  • Monitor chunk timing for stalls\n";
    echo "  • Handle network interruptions\n";
    echo "  • Buffer results for display\n";
    echo "  • Log errors for debugging\n";
}

// Run the example if executed directly
if (php_sapi_name() === 'cli') {
    main();
}
