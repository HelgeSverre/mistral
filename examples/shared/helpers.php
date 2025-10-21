<?php

/** @noinspection PhpUnused */

/**
 * Helper functions for Mistral PHP SDK examples
 *
 * Common utilities used across all examples
 */

use HelgeSverre\Mistral\Dto\Chat\ChatCompletionResponse;
use HelgeSverre\Mistral\Dto\Chat\ChatCompletionStreamResponse;
use Saloon\Exceptions\Request\Statuses\BadRequestException;
use Saloon\Exceptions\Request\Statuses\GatewayTimeOutException;
use Saloon\Exceptions\Request\Statuses\NotFoundException;
use Saloon\Exceptions\Request\Statuses\RequestTimeOutException;
use Saloon\Exceptions\Request\Statuses\TooManyRequestsException;
use Saloon\Exceptions\Request\Statuses\UnauthorizedException;

/**
 * Print a formatted response from chat completion
 *
 * @param  bool  $showMetadata  Whether to display token usage and model info
 */
function printResponse(ChatCompletionResponse $response, bool $showMetadata = false): void
{
    echo "\n📝 Response:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    foreach ($response->choices as $choice) {
        echo $choice->message->content."\n";

        if ($choice->finishReason) {
            echo "\n[Finish reason: {$choice->finishReason}]\n";
        }
    }

    if ($showMetadata && $response->usage) {
        echo "\n📊 Usage Statistics:\n";
        echo "  • Prompt tokens: {$response->usage->promptTokens}\n";
        echo "  • Completion tokens: {$response->usage->completionTokens}\n";
        echo "  • Total tokens: {$response->usage->totalTokens}\n";

        if ($response->model) {
            echo "  • Model: {$response->model}\n";
        }
    }

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
}

/**
 * Print streaming response chunks in real-time
 *
 * @param  bool  $showProgress  Whether to show a progress indicator
 */
function printStream(iterable $stream, bool $showProgress = false): void
{
    echo "\n📝 Streaming Response:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    $fullContent = '';
    $chunkCount = 0;

    foreach ($stream as $chunk) {
        if ($chunk instanceof ChatCompletionStreamResponse) {
            foreach ($chunk->choices as $choice) {
                if (isset($choice->delta->content)) {
                    $content = $choice->delta->content;
                    echo $content;
                    $fullContent .= $content;
                    $chunkCount++;

                    if ($showProgress && $chunkCount % 10 === 0) {
                        echo ' ';
                    }
                }

                if ($choice->finishReason) {
                    echo "\n\n[Stream finished: {$choice->finishReason}]\n";
                }
            }
        }
    }

    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    if ($showProgress) {
        echo "📊 Stream Statistics:\n";
        echo "  • Total chunks: {$chunkCount}\n";
        echo '  • Total characters: '.strlen($fullContent)."\n\n";
    }
}

/**
 * Measure execution time of a callback
 *
 * @return mixed The result of the callback
 */
function measureTime(callable $callback, string $label = 'Operation'): mixed
{
    $start = microtime(true);

    $result = $callback();

    $duration = microtime(true) - $start;
    $formatted = number_format($duration, 3);

    echo "⏱️ {$label} completed in {$formatted} seconds\n";

    return $result;
}

/**
 * Handle API errors gracefully
 *
 * @param  bool  $exitOnError  Whether to exit the script
 */
function handleError(Throwable $error, bool $exitOnError = true): void
{
    echo "\n❌ Error occurred:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    // Check for specific error types
    if ($error instanceof UnauthorizedException) {
        echo "Authentication failed. Please check your API key.\n";
        echo "You can get an API key at: https://console.mistral.ai/\n";
    } elseif ($error instanceof TooManyRequestsException) {
        echo "Rate limit exceeded. Please wait and try again.\n";
        echo "Consider implementing exponential backoff for production use.\n";
    } elseif ($error instanceof BadRequestException) {
        echo "Bad request. Please check your parameters.\n";
    } elseif ($error instanceof NotFoundException) {
        echo "Resource not found. Please check the endpoint or resource ID.\n";
    } elseif ($error instanceof RequestTimeOutException || $error instanceof GatewayTimeOutException) {
        echo "Request timed out. Consider increasing the timeout or reducing payload size.\n";
    } else {
        echo 'Error: '.$error->getMessage()."\n";
    }

    if ($_ENV['DEBUG_MODE'] ?? false) {
        echo "\n🔍 Debug Information:\n";
        echo '  • Exception: '.get_class($error)."\n";
        echo '  • File: '.$error->getFile().':'.$error->getLine()."\n";
        echo "\nStack trace:\n".$error->getTraceAsString()."\n";
    }

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    if ($exitOnError) {
        exit(1);
    }
}

/**
 * Load a fixture file
 *
 * @throws RuntimeException if file not found
 */
function loadFixture(string $filename): string
{
    $paths = [
        FIXTURES_DIR.'/'.$filename,
        __DIR__.'/../fixtures/'.$filename,
        __DIR__.'/../../tests/Fixtures/'.$filename,
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            return file_get_contents($path);
        }
    }

    throw new RuntimeException("Fixture file not found: {$filename}");
}

/**
 * Pretty print JSON data
 */
function printJson(mixed $data, string $label = 'JSON Data'): void
{
    echo "\n📋 {$label}:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
}

/**
 * Calculate cosine similarity between two embedding vectors
 *
 * @return float Similarity score between -1 and 1
 */
function cosineSimilarity(array $vectorA, array $vectorB): float
{
    if (count($vectorA) !== count($vectorB)) {
        throw new InvalidArgumentException('Vectors must have the same dimension');
    }

    $dotProduct = 0;
    $magnitudeA = 0;
    $magnitudeB = 0;

    for ($i = 0; $i < count($vectorA); $i++) {
        $dotProduct += $vectorA[$i] * $vectorB[$i];
        $magnitudeA += $vectorA[$i] * $vectorA[$i];
        $magnitudeB += $vectorB[$i] * $vectorB[$i];
    }

    $magnitudeA = sqrt($magnitudeA);
    $magnitudeB = sqrt($magnitudeB);

    if ($magnitudeA == 0 || $magnitudeB == 0) {
        return 0;
    }

    return $dotProduct / ($magnitudeA * $magnitudeB);
}

/**
 * Format file size in human-readable format
 */
function formatBytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    $size = (float) $bytes;

    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }

    return sprintf('%.2f %s', $size, $units[$i]);
}

/**
 * Format a number as a percentage
 */
function formatPercent(float $value, int $decimals = 1): string
{
    return number_format($value * 100, $decimals).'%';
}

/**
 * Create a progress bar for long-running operations
 */
function showProgress(int $current, int $total, int $width = 50): void
{
    $percentage = ($current / $total) * 100;
    $filled = (int) (($current / $total) * $width);
    $empty = $width - $filled;

    echo "\r[".str_repeat('█', $filled).str_repeat('░', $empty).'] ';
    echo sprintf('%.1f%% (%d/%d)', $percentage, $current, $total);

    if ($current >= $total) {
        echo "\n";
    }
}

/**
 * Retry a function with exponential backoff
 *
 * @param  int  $initialDelay  Delay in seconds
 *
 * @throws Throwable
 */
function retryWithBackoff(callable $function, int $maxAttempts = 3, int $initialDelay = 1): mixed
{
    $attempt = 1;
    $delay = $initialDelay;

    while ($attempt <= $maxAttempts) {
        try {
            return $function();
        } catch (Throwable $e) {
            if ($attempt >= $maxAttempts) {
                throw $e;
            }

            echo "⚠️ Attempt {$attempt} failed. Retrying in {$delay} seconds...\n";
            sleep($delay);

            $attempt++;
            $delay *= 2; // Exponential backoff
        }
    }

    throw new RuntimeException('Max retry attempts exceeded');
}

/**
 * Chunk text into smaller pieces for processing
 */
function chunkText(string $text, int $maxChunkSize = 1000, int $overlap = 100): array
{
    $chunks = [];
    $sentences = preg_split('/(?<=[.!?])\s+/', $text);

    $currentChunk = '';
    $currentSize = 0;

    foreach ($sentences as $sentence) {
        $sentenceSize = strlen($sentence);

        if ($currentSize + $sentenceSize > $maxChunkSize && ! empty($currentChunk)) {
            $chunks[] = trim($currentChunk);

            // Keep overlap from the end of the current chunk
            if ($overlap > 0) {
                $overlapText = substr($currentChunk, -$overlap);
                $currentChunk = $overlapText.' '.$sentence;
                $currentSize = strlen($currentChunk);
            } else {
                $currentChunk = $sentence;
                $currentSize = $sentenceSize;
            }
        } else {
            $currentChunk .= ($currentChunk ? ' ' : '').$sentence;
            $currentSize += $sentenceSize;
        }
    }

    if (! empty($currentChunk)) {
        $chunks[] = trim($currentChunk);
    }

    return $chunks;
}

/**
 * Display a formatted title for example sections
 */
function displayTitle(string $title, string $emoji = '🚀'): void
{
    $line = str_repeat('═', 50);
    echo "\n{$emoji} {$title}\n";
    echo "{$line}\n\n";
}

/**
 * Display a formatted section header
 */
function displaySection(string $section): void
{
    echo "\n▶️ {$section}\n";
    echo str_repeat('─', 40)."\n";
}

/**
 * Format and display model information
 */
function displayModel(object $model): void
{
    echo "\n🤖 Model Information:\n";
    echo "  • ID: {$model->id}\n";
    echo '  • Created: '.date('Y-m-d H:i:s', $model->created)."\n";
    echo "  • Owner: {$model->ownedBy}\n";

    if (isset($model->capabilities)) {
        echo "  • Capabilities:\n";
        foreach ($model->capabilities as $capability => $enabled) {
            $status = $enabled ? '✅' : '❌';
            echo "    - {$capability}: {$status}\n";
        }
    }
    echo "\n";
}

/**
 * Validate environment setup
 *
 * @param  array  $required  Required environment variables
 *
 * @throws RuntimeException if validation fails
 */
function validateEnvironment(array $required = ['MISTRAL_API_KEY']): void
{
    $missing = [];

    foreach ($required as $var) {
        if (! isset($_ENV[$var]) || $_ENV[$var] === '') {
            $missing[] = $var;
        }
    }

    if (! empty($missing)) {
        throw new RuntimeException(
            'Missing required environment variables: '.implode(', ', $missing)."\n".
            'Please check your .env file.'
        );
    }
}

/**
 * Log debug information to file
 */
function debugLog(string $message, mixed $data = null): void
{
    if (! ($_ENV['DEBUG_MODE'] ?? false)) {
        return;
    }

    $logFile = $_ENV['LOG_FILE'] ?? '/tmp/mistral-examples.log';
    $timestamp = date('Y-m-d H:i:s');

    $log = "[{$timestamp}] {$message}";
    if ($data !== null) {
        $log .= "\n".json_encode($data, JSON_PRETTY_PRINT);
    }
    $log .= "\n\n";

    file_put_contents($logFile, $log, FILE_APPEND | LOCK_EX);
}
