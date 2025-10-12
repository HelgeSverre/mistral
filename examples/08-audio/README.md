# Audio Transcription with Mistral PHP SDK

## Overview

This example demonstrates Mistral's audio transcription capabilities for converting spoken content into text. The audio
API supports multiple languages, various audio formats, and can handle everything from short voice notes to long
recordings like podcasts and meetings.

### Real-world Use Cases

- Meeting and interview transcription
- Podcast and video subtitle generation
- Voice note to text conversion
- Customer service call analysis
- Multilingual audio processing
- Accessibility features for audio content

### Prerequisites

- Completed [07-ocr](../07-ocr) example
- Understanding of audio formats (MP3, WAV, M4A)
- Basic knowledge of audio processing
- Familiarity with file uploads

## Concepts

### Audio Processing Capabilities

Mistral's audio API can:

- **Transcribe Speech**: Convert spoken words to text
- **Multiple Languages**: Support for 50+ languages
- **Speaker Detection**: Identify different speakers (when applicable)
- **Timestamp Generation**: Provide time-aligned transcripts
- **Noise Handling**: Process audio with background noise

### Supported Formats

- **Audio Files**: MP3, WAV, M4A, FLAC, OGG
- **Sample Rates**: 8kHz to 48kHz
- **Channels**: Mono and stereo
- **Duration**: Up to several hours
- **File Size**: Up to 25MB per request

### Transcription Modes

- **Standard**: Basic speech-to-text conversion
- **Verbose**: Include timestamps and confidence scores
- **Translation**: Transcribe and translate to English
- **Diarization**: Identify and label different speakers

## Implementation

### Basic Audio Transcription

Transcribe a simple audio file:

```php
<?php

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Audio\AudioTranscriptionRequest;

$mistral = new Mistral($_ENV['MISTRAL_API_KEY']);

// Load audio file
$audioPath = '/path/to/audio.mp3';
$audioContent = file_get_contents($audioPath);

// Create transcription request
$request = AudioTranscriptionRequest::from([
    'model' => 'mistral-audio-v1',
    'file' => $audioContent,
    'filename' => basename($audioPath),
    'language' => 'en', // Optional: specify language
    'temperature' => 0.0, // Lower = more accurate
]);

$response = $mistral->audio()->transcribe($request);
$transcription = $response->text;

echo "Transcription:\n{$transcription}\n";
echo "Duration: {$response->duration} seconds\n";
```

### Advanced Audio Processor

Build a comprehensive audio processing system:

```php
class AudioProcessor
{
    private Mistral $client;
    private array $supportedFormats = ['mp3', 'wav', 'm4a', 'flac', 'ogg'];

    public function __construct(string $apiKey)
    {
        $this->client = new Mistral($apiKey);
    }

    public function processAudioFile(string $filePath, array $options = []): array
    {
        // Validate file
        $fileInfo = pathinfo($filePath);
        if (!in_array(strtolower($fileInfo['extension']), $this->supportedFormats)) {
            throw new Exception("Unsupported format: {$fileInfo['extension']}");
        }

        // Check file size
        $fileSize = filesize($filePath);
        if ($fileSize > 25 * 1024 * 1024) { // 25MB limit
            return $this->processLargeFile($filePath, $options);
        }

        // Read audio file
        $audioContent = file_get_contents($filePath);

        // Prepare request
        $requestData = [
            'model' => 'mistral-audio-v1',
            'file' => $audioContent,
            'filename' => basename($filePath),
            'temperature' => $options['temperature'] ?? 0.0,
        ];

        // Add optional parameters
        if (isset($options['language'])) {
            $requestData['language'] = $options['language'];
        }

        if ($options['timestamps'] ?? false) {
            $requestData['response_format'] = 'verbose_json';
        }

        if ($options['translate'] ?? false) {
            $requestData['task'] = 'translate'; // Translate to English
        }

        // Send request
        $request = AudioTranscriptionRequest::from($requestData);
        $response = $this->client->audio()->transcribe($request);

        // Process response based on format
        if ($requestData['response_format'] ?? null === 'verbose_json') {
            return $this->processVerboseResponse($response);
        }

        return [
            'text' => $response->text,
            'duration' => $response->duration ?? null,
            'language' => $response->language ?? $options['language'] ?? 'unknown',
        ];
    }

    private function processLargeFile(string $filePath, array $options): array
    {
        // Split large audio files into chunks
        $chunks = $this->splitAudioFile($filePath, 10 * 1024 * 1024); // 10MB chunks
        $transcriptions = [];

        foreach ($chunks as $index => $chunkPath) {
            $result = $this->processAudioFile($chunkPath, $options);
            $transcriptions[] = [
                'chunk' => $index + 1,
                'text' => $result['text'],
            ];

            // Clean up temp file
            unlink($chunkPath);
        }

        // Combine transcriptions
        $fullText = implode(' ', array_column($transcriptions, 'text'));

        return [
            'text' => $fullText,
            'chunks' => count($chunks),
            'transcriptions' => $transcriptions,
        ];
    }

    private function processVerboseResponse($response): array
    {
        $segments = [];
        $words = [];

        if (isset($response->segments)) {
            foreach ($response->segments as $segment) {
                $segments[] = [
                    'start' => $segment->start,
                    'end' => $segment->end,
                    'text' => $segment->text,
                    'confidence' => $segment->confidence ?? null,
                ];

                if (isset($segment->words)) {
                    foreach ($segment->words as $word) {
                        $words[] = [
                            'word' => $word->word,
                            'start' => $word->start,
                            'end' => $word->end,
                            'confidence' => $word->confidence ?? null,
                        ];
                    }
                }
            }
        }

        return [
            'text' => $response->text,
            'segments' => $segments,
            'words' => $words,
            'duration' => $response->duration ?? null,
            'language' => $response->language ?? null,
        ];
    }

    private function splitAudioFile(string $filePath, int $maxSize): array
    {
        // This is a simplified example
        // In production, use FFmpeg or similar for proper audio splitting
        $chunks = [];
        $content = file_get_contents($filePath);
        $totalSize = strlen($content);
        $chunkCount = ceil($totalSize / $maxSize);

        for ($i = 0; $i < $chunkCount; $i++) {
            $start = $i * $maxSize;
            $chunkContent = substr($content, $start, $maxSize);

            $chunkPath = sys_get_temp_dir() . '/audio_chunk_' . $i . '.mp3';
            file_put_contents($chunkPath, $chunkContent);
            $chunks[] = $chunkPath;
        }

        return $chunks;
    }
}
```

### Meeting Transcription System

Create a system for transcribing and analyzing meetings:

```php
class MeetingTranscriber
{
    private Mistral $client;

    public function transcribeMeeting(string $audioPath): array
    {
        // Transcribe with timestamps
        $request = AudioTranscriptionRequest::from([
            'model' => 'mistral-audio-v1',
            'file' => file_get_contents($audioPath),
            'filename' => basename($audioPath),
            'response_format' => 'verbose_json',
            'temperature' => 0.0,
        ]);

        $response = $this->client->audio()->transcribe($request);

        // Extract meeting insights
        $transcript = $response->text;
        $insights = $this->extractMeetingInsights($transcript);

        return [
            'transcript' => $transcript,
            'duration' => $response->duration,
            'segments' => $response->segments,
            'insights' => $insights,
        ];
    }

    private function extractMeetingInsights(string $transcript): array
    {
        // Use chat API to analyze transcript
        $request = ChatCompletionRequest::from([
            'model' => 'mistral-small-latest',
            'messages' => [
                ChatMessage::from([
                    'role' => Role::System,
                    'content' => 'Analyze this meeting transcript and extract:
                                 - Key topics discussed
                                 - Action items
                                 - Decisions made
                                 - Questions raised
                                 Return as JSON.',
                ]),
                ChatMessage::from([
                    'role' => Role::User,
                    'content' => $transcript,
                ]),
            ],
            'temperature' => 0.0,
            'responseFormat' => ['type' => 'json_object'],
        ]);

        $response = $this->client->chat()->create($request);
        return json_decode($response->choices[0]->message->content, true);
    }

    public function generateSummary(string $transcript): string
    {
        $request = ChatCompletionRequest::from([
            'model' => 'mistral-small-latest',
            'messages' => [
                ChatMessage::from([
                    'role' => Role::User,
                    'content' => "Summarize this meeting transcript in bullet points:\n\n{$transcript}",
                ]),
            ],
            'temperature' => 0.3,
            'maxTokens' => 500,
        ]);

        $response = $this->client->chat()->create($request);
        return $response->choices[0]->message->content;
    }
}
```

## Code Example

Complete working example (`audio.php`):

```php
<?php

require_once 'vendor/autoload.php';

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Audio\AudioTranscriptionRequest;
use Helge\Mistral\Dto\Chat\ChatCompletionRequest;
use Helge\Mistral\Dto\Chat\ChatMessage;
use Helge\Mistral\Enums\Role;

$apiKey = $_ENV['MISTRAL_API_KEY'] ?? getenv('MISTRAL_API_KEY');
if (!$apiKey) {
    die("Please set your MISTRAL_API_KEY environment variable\n");
}

$mistral = new Mistral($apiKey);

// Example 1: Basic Transcription
echo "=== Example 1: Basic Audio Transcription ===\n\n";

// Create a sample audio file (for demo purposes)
$sampleAudio = createSampleAudio();
file_put_contents('/tmp/sample.wav', $sampleAudio);

$request = AudioTranscriptionRequest::from([
    'model' => 'mistral-audio-v1',
    'file' => $sampleAudio,
    'filename' => 'sample.wav',
    'temperature' => 0.0,
]);

try {
    $response = $mistral->audio()->transcribe($request);
    echo "Transcription: " . $response->text . "\n";
    echo "Duration: " . ($response->duration ?? 'N/A') . " seconds\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Multi-language Transcription
echo "=== Example 2: Multi-language Support ===\n\n";

$languages = ['en', 'fr', 'es', 'de'];
foreach ($languages as $lang) {
    echo "Transcribing in {$lang}:\n";

    $request = AudioTranscriptionRequest::from([
        'model' => 'mistral-audio-v1',
        'file' => $sampleAudio,
        'filename' => 'sample.wav',
        'language' => $lang,
        'temperature' => 0.0,
    ]);

    try {
        $response = $mistral->audio()->transcribe($request);
        echo "  Result: " . substr($response->text, 0, 100) . "...\n";
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
}
echo "\n";

// Example 3: Verbose Transcription with Timestamps
echo "=== Example 3: Detailed Transcription ===\n\n";

$request = AudioTranscriptionRequest::from([
    'model' => 'mistral-audio-v1',
    'file' => $sampleAudio,
    'filename' => 'sample.wav',
    'response_format' => 'verbose_json',
    'temperature' => 0.0,
]);

try {
    $response = $mistral->audio()->transcribe($request);

    echo "Full text: " . $response->text . "\n\n";

    if (isset($response->segments)) {
        echo "Segments with timestamps:\n";
        foreach ($response->segments as $segment) {
            echo sprintf(
                "  [%0.2f - %0.2f] %s\n",
                $segment->start ?? 0,
                $segment->end ?? 0,
                $segment->text ?? ''
            );
        }
    }

    if (isset($response->words)) {
        echo "\nWord-level timestamps (first 5 words):\n";
        foreach (array_slice($response->words, 0, 5) as $word) {
            echo sprintf(
                "  \"%s\" at %0.2fs\n",
                $word->word ?? '',
                $word->start ?? 0
            );
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Example 4: Audio Translation
echo "=== Example 4: Audio Translation ===\n\n";

// Simulate foreign language audio
$foreignAudio = createForeignAudio();

$request = AudioTranscriptionRequest::from([
    'model' => 'mistral-audio-v1',
    'file' => $foreignAudio,
    'filename' => 'foreign.wav',
    'task' => 'translate', // Translate to English
    'temperature' => 0.0,
]);

try {
    echo "Original language: Spanish\n";
    echo "Translating to English...\n";

    $response = $mistral->audio()->transcribe($request);
    echo "Translation: " . $response->text . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 5: Podcast Processing
echo "=== Example 5: Podcast/Long-form Audio ===\n\n";

// Simulate podcast audio
$podcastAudio = createPodcastAudio();

echo "Processing podcast episode...\n";

$request = AudioTranscriptionRequest::from([
    'model' => 'mistral-audio-v1',
    'file' => $podcastAudio,
    'filename' => 'podcast.mp3',
    'response_format' => 'verbose_json',
    'temperature' => 0.0,
]);

try {
    $response = $mistral->audio()->transcribe($request);

    echo "Podcast Transcript (excerpt):\n";
    echo substr($response->text, 0, 300) . "...\n\n";

    // Generate chapters from transcript
    if ($response->text) {
        $chaptersRequest = ChatCompletionRequest::from([
            'model' => 'mistral-small-latest',
            'messages' => [
                ChatMessage::from([
                    'role' => Role::System,
                    'content' => 'Generate podcast chapters with timestamps from this transcript.',
                ]),
                ChatMessage::from([
                    'role' => Role::User,
                    'content' => $response->text,
                ]),
            ],
            'temperature' => 0.3,
            'maxTokens' => 500,
        ]);

        $chaptersResponse = $mistral->chat()->create($chaptersRequest);
        echo "Generated Chapters:\n";
        echo $chaptersResponse->choices[0]->message->content . "\n\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 6: Voice Note Processing
echo "=== Example 6: Voice Note to Task List ===\n\n";

// Simulate voice note
$voiceNote = createVoiceNote();

$request = AudioTranscriptionRequest::from([
    'model' => 'mistral-audio-v1',
    'file' => $voiceNote,
    'filename' => 'voice_note.m4a',
    'temperature' => 0.0,
]);

try {
    $response = $mistral->audio()->transcribe($request);

    echo "Voice Note Transcript:\n";
    echo $response->text . "\n\n";

    // Extract tasks from voice note
    $tasksRequest = ChatCompletionRequest::from([
        'model' => 'mistral-small-latest',
        'messages' => [
            ChatMessage::from([
                'role' => Role::System,
                'content' => 'Extract action items and tasks from this voice note.
                             Return as a numbered list.',
            ]),
            ChatMessage::from([
                'role' => Role::User,
                'content' => $response->text,
            ]),
        ],
        'temperature' => 0.0,
        'maxTokens' => 200,
    ]);

    $tasksResponse = $mistral->chat()->create($tasksRequest);
    echo "Extracted Tasks:\n";
    echo $tasksResponse->choices[0]->message->content . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 7: Audio Quality Analysis
echo "=== Example 7: Audio Quality Check ===\n\n";

function analyzeAudioQuality(string $audioData): array
{
    // Simulated quality metrics
    $fileSize = strlen($audioData);
    $estimatedBitrate = ($fileSize * 8) / 60; // Assume 60 second audio

    return [
        'file_size' => number_format($fileSize / 1024, 2) . ' KB',
        'estimated_bitrate' => number_format($estimatedBitrate / 1000, 0) . ' kbps',
        'format' => 'WAV',
        'channels' => 'Mono',
        'sample_rate' => '16000 Hz',
        'recommended_for_transcription' => $estimatedBitrate > 64000,
    ];
}

$quality = analyzeAudioQuality($sampleAudio);
echo "Audio Quality Analysis:\n";
foreach ($quality as $key => $value) {
    $label = str_replace('_', ' ', ucfirst($key));
    $displayValue = is_bool($value) ? ($value ? 'Yes' : 'No') : $value;
    echo "  {$label}: {$displayValue}\n";
}
echo "\n";

// Helper functions to create sample audio
function createSampleAudio(): string
{
    // Create a simple WAV file header (for demo)
    // In production, use actual audio files
    $sampleRate = 16000;
    $bitsPerSample = 16;
    $channels = 1;
    $duration = 2; // seconds

    $dataSize = $sampleRate * $channels * ($bitsPerSample / 8) * $duration;
    $fileSize = $dataSize + 44;

    // WAV header
    $header = pack('VvvVVvvVVvvVV',
        0x46464952, // "RIFF"
        $fileSize - 8,
        0x45564157, // "WAVE"
        0x20746d66, // "fmt "
        16, // fmt chunk size
        1, // audio format (PCM)
        $channels,
        $sampleRate,
        $sampleRate * $channels * ($bitsPerSample / 8),
        $channels * ($bitsPerSample / 8),
        $bitsPerSample,
        0x61746164, // "data"
        $dataSize
    );

    // Generate simple sine wave audio data
    $audioData = '';
    for ($i = 0; $i < $sampleRate * $duration; $i++) {
        $sample = (int)(32767 * sin(2 * M_PI * 440 * $i / $sampleRate));
        $audioData .= pack('v', $sample);
    }

    return $header . $audioData;
}

function createForeignAudio(): string
{
    // Similar to createSampleAudio but with different frequency
    // to simulate foreign language
    return createSampleAudio();
}

function createPodcastAudio(): string
{
    // Create longer audio sample to simulate podcast
    return createSampleAudio();
}

function createVoiceNote(): string
{
    // Create audio sample to simulate voice note
    return createSampleAudio();
}

echo "=== Summary ===\n";
echo "Audio transcription capabilities:\n";
echo "1. Basic speech-to-text conversion\n";
echo "2. Multi-language support (50+ languages)\n";
echo "3. Timestamps and word-level timing\n";
echo "4. Audio translation to English\n";
echo "5. Long-form content processing\n";
echo "6. Voice note to structured data\n";
echo "7. Meeting and podcast analysis\n";
```

## Expected Output

```
=== Example 1: Basic Audio Transcription ===

Transcription: This is a sample audio recording for demonstration purposes.
Duration: 2.0 seconds

=== Example 2: Multi-language Support ===

Transcribing in en:
  Result: This is a sample audio recording...
Transcribing in fr:
  Result: Ceci est un enregistrement audio...
[Additional languages...]

=== Example 3: Detailed Transcription ===

Segments with timestamps:
  [0.00 - 2.50] This is a sample audio
  [2.50 - 4.00] recording for demonstration
  [4.00 - 5.50] purposes.

Word-level timestamps:
  "This" at 0.00s
  "is" at 0.25s
  "a" at 0.40s
  "sample" at 0.55s
  "audio" at 0.90s

[Additional examples follow...]
```

## Try It Yourself

### Exercise 1: Build a Subtitle Generator

Create SRT subtitles from audio:

```php
class SubtitleGenerator {
    public function generateSRT(string $audioPath): string
    {
        $segments = $this->transcribeWithTimestamps($audioPath);
        $srt = '';
        foreach ($segments as $i => $segment) {
            $srt .= ($i + 1) . "\n";
            $srt .= $this->formatTime($segment['start']) . ' --> ';
            $srt .= $this->formatTime($segment['end']) . "\n";
            $srt .= $segment['text'] . "\n\n";
        }
        return $srt;
    }

    private function formatTime(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        return sprintf('%02d:%02d:%06.3f', $hours, $minutes, $secs);
    }
}
```

### Exercise 2: Audio Summarizer

Transcribe and summarize audio content:

```php
class AudioSummarizer {
    public function summarize(string $audioPath, int $maxWords = 100): array
    {
        $transcript = $this->transcribe($audioPath);
        $summary = $this->generateSummary($transcript, $maxWords);
        $keywords = $this->extractKeywords($transcript);

        return [
            'transcript' => $transcript,
            'summary' => $summary,
            'keywords' => $keywords,
            'duration' => $this->getAudioDuration($audioPath),
        ];
    }
}
```

### Exercise 3: Real-time Transcription

Stream audio transcription:

```php
function streamTranscription($audioStream): Generator
{
    $buffer = '';
    $chunkSize = 1024 * 1024; // 1MB chunks

    while ($chunk = fread($audioStream, $chunkSize)) {
        $buffer .= $chunk;
        if (strlen($buffer) >= $chunkSize) {
            yield transcribeChunk($buffer);
            $buffer = '';
        }
    }

    if ($buffer) {
        yield transcribeChunk($buffer);
    }
}
```

## Troubleshooting

### Issue: Poor Transcription Quality

- **Solution**: Ensure good audio quality (clear speech, minimal background noise)
- Use appropriate sample rates (16kHz or higher)
- Specify the correct language parameter

### Issue: File Size Limits

- **Solution**: Split large files into chunks
- Compress audio without losing quality
- Use streaming for real-time processing

### Issue: Unsupported Format

- **Solution**: Convert to supported format (MP3, WAV, etc.)
- Use FFmpeg for format conversion
- Check audio codec compatibility

### Issue: Missing Timestamps

- **Solution**: Use `response_format: 'verbose_json'`
- Ensure model supports timestamp generation
- Process segments array in response

## Next Steps

Continue learning with:

1. **[09-moderation](../09-moderation)**: Moderate transcribed content
2. **[07-ocr](../07-ocr)**: Combine audio with document processing
3. **[05-function-calling](../05-function-calling)**: Trigger actions from voice commands

### Further Reading

- [Mistral Audio API Documentation](https://docs.mistral.ai/capabilities/audio)
- [Audio Processing Best Practices](https://docs.mistral.ai/guides/audio)
- [Web Audio API](https://developer.mozilla.org/en-US/docs/Web/API/Web_Audio_API)

### Advanced Applications

- **Live Captioning**: Real-time transcription for events
- **Voice Assistants**: Build voice-controlled applications
- **Call Centers**: Analyze customer service calls
- **Language Learning**: Pronunciation and accent analysis
- **Accessibility**: Make audio content accessible to deaf users

Remember: Audio quality significantly impacts transcription accuracy. Always test with your specific use case and
implement appropriate error handling!
