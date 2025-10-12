<?php

/**
 * Streaming Chat Completions
 *
 * Description: Learn how to stream responses in real-time for better UX
 * Use Case: Interactive chatbots, live content generation, responsive applications
 * Prerequisites: MISTRAL_API_KEY in .env file
 *
 * @see https://docs.mistral.ai/capabilities/completion/#streaming
 */

declare(strict_types=1);

require_once __DIR__.'/../shared/bootstrap.php';

use HelgeSverre\Mistral\Dto\Chat\StreamedChatCompletionResponse;
use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Enums\Role;
use HelgeSverre\Mistral\Mistral;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\Statuses\GatewayTimeoutException;
use Saloon\Exceptions\Request\Statuses\RequestTimeOutException;

/**
 * Main execution function
 */
function main(): void
{
    displayTitle('Streaming Chat Completions', '🌊');

    $mistral = createMistralClient();

    try {
        // Example 1: Basic streaming
        basicStreaming($mistral);

        // Example 2: Streaming with metadata
        streamingWithMetadata($mistral);

        // Example 3: Measuring streaming performance
        streamingPerformance($mistral);

        // Example 4: Error handling in streams
        streamingErrorHandling($mistral);

    } catch (Throwable $e) {
        handleError($e);
    }
}

/**
 * Example 1: Basic streaming response
 */
function basicStreaming(Mistral $mistral): void
{
    displaySection('Example 1: Basic Streaming');
    echo "Streaming a response in real-time...\n\n";

    // Streaming provides chunks of text as they are generated
    // This improves perceived latency and provides better UX
    // Perfect for interactive applications and long responses

    $messages = [
        [
            'role' => Role::user->value,
            'content' => 'Write a short paragraph about the benefits of streaming API responses.',
        ],
    ];

    echo "🌊 Streaming response:\n";
    echo str_repeat('═', 50)."\n";

    // The createStreamed method returns a Generator
    // Each chunk is yielded as it arrives from the API
    $stream = $mistral->chat()->createStreamed(
        messages: $messages,
        model: Model::small->value,
        temperature: 0.7,
        maxTokens: 200,
    );

    // Process each chunk as it arrives
    $fullText = '';
    $chunkCount = 0;

    foreach ($stream as $chunk) {
        /** @var StreamedChatCompletionResponse $chunk */
        $choice = $chunk->choices->first();
        if (! $choice) {
            continue;
        }

        if (isset($choice->delta->content)) {
            $content = $choice->delta->content;

            // Display the chunk immediately (simulates real-time output)
            echo $content;
            flush(); // Force output to display immediately

            $fullText .= $content;
            $chunkCount++;
        }

        // Check for completion
        if (isset($choice->finishReason)) {
            echo "\n\n[Stream completed: {$choice->finishReason}]\n";
        }
    }

    echo str_repeat('═', 50)."\n\n";

    echo "📊 Stream Statistics:\n";
    echo "  • Total chunks received: {$chunkCount}\n";
    echo '  • Total characters: '.strlen($fullText)."\n";
    if ($chunkCount > 0) {
        echo '  • Average chunk size: '.round(strlen($fullText) / $chunkCount, 2)." chars\n\n";
    } else {
        echo "  • Average chunk size: N/A\n\n";
    }

    echo "💡 Benefits of Streaming:\n";
    echo "  • Improved perceived latency\n";
    echo "  • Better user experience\n";
    echo "  • See responses as they generate\n";
    echo "  • Handle long responses gracefully\n\n";
}

/**
 * Example 2: Streaming with metadata and progress tracking
 */
function streamingWithMetadata(Mistral $mistral): void
{
    displaySection('Example 2: Streaming with Metadata');
    echo "Tracking detailed metadata during streaming...\n\n";

    $messages = [
        [
            'role' => Role::system->value,
            'content' => 'You are a helpful assistant. Provide detailed explanations.',
        ],
        [
            'role' => Role::user->value,
            'content' => 'Explain how HTTP/2 improves web performance.',
        ],
    ];

    echo "🌊 Streaming with progress indicator:\n";
    echo str_repeat('═', 50)."\n";

    $stream = $mistral->chat()->createStreamed(
        messages: $messages,
        model: Model::small->value,
        maxTokens: 300,
    );

    $fullText = '';
    $chunkCount = 0;
    $startTime = microtime(true);
    $firstChunkTime = null;

    foreach ($stream as $chunk) {
        if ($chunkCount === 0) {
            // Measure time to first chunk (TTFC)
            $firstChunkTime = microtime(true) - $startTime;
        }

        $choice = $chunk->choices->first();
        if (! $choice) {
            continue;
        }

        if (isset($choice->delta->content)) {
            $content = $choice->delta->content;
            echo $content;
            flush();

            $fullText .= $content;
            $chunkCount++;

            // Show progress indicator every 10 chunks
            if ($chunkCount % 10 === 0) {
                // You can add visual indicators here if needed
            }
        }

        // Check finish reason
        if (isset($choice->finishReason)) {
            $finishReason = $choice->finishReason;
            echo "\n\n";

            $totalTime = microtime(true) - $startTime;

            echo str_repeat('═', 50)."\n\n";
            echo "📊 Detailed Stream Metrics:\n";
            if ($firstChunkTime !== null) {
                echo '  • Time to first chunk: '.number_format($firstChunkTime, 3)." seconds\n";
            }
            echo '  • Total time: '.number_format($totalTime, 3)." seconds\n";
            echo "  • Total chunks: {$chunkCount}\n";
            echo '  • Total characters: '.strlen($fullText)."\n";
            echo '  • Characters per second: '.round(strlen($fullText) / $totalTime, 2)."\n";
            echo '  • Chunks per second: '.round($chunkCount / $totalTime, 2)."\n";
            echo "  • Finish reason: {$finishReason}\n";
            echo "  • Model: {$chunk->model}\n\n";
        }
    }

    echo "💡 Metrics to Monitor:\n";
    echo "  • Time to first chunk (TTFC): User sees response\n";
    echo "  • Streaming speed: Content generation rate\n";
    echo "  • Total time: Complete response time\n";
    echo "  • Chunk consistency: Network stability\n\n";
}

/**
 * Example 3: Performance comparison: streaming vs non-streaming
 */
function streamingPerformance(Mistral $mistral): void
{
    displaySection('Example 3: Performance Comparison');
    echo "Comparing streaming vs non-streaming response times...\n\n";

    $messages = [
        [
            'role' => Role::user->value,
            'content' => 'List and explain 5 design patterns in software engineering.',
        ],
    ];

    // Test 1: Non-streaming (traditional)
    echo "🔄 Non-Streaming Request:\n";
    echo str_repeat('─', 40)."\n";

    $start = microtime(true);

    $response = $mistral->chat()->create(
        messages: $messages,
        model: Model::small->value,
        maxTokens: 300,
    );

    $nonStreamTime = microtime(true) - $start;
    $dto = $response->dto();
    $choice = $dto->choices->first();
    if (! $choice) {
        echo "❌ No response received\n\n";

        return;
    }
    $content = $choice->message->content;

    echo "✅ Response received\n";
    echo '  • Total time: '.number_format($nonStreamTime, 3)." seconds\n";
    echo '  • Content length: '.strlen($content)." characters\n";
    echo "  • Tokens: {$dto->usage->completionTokens}\n\n";

    // Test 2: Streaming
    echo "🌊 Streaming Request:\n";
    echo str_repeat('─', 40)."\n";

    $start = microtime(true);
    $firstChunkTime = null;
    $chunkCount = 0;

    $stream = $mistral->chat()->createStreamed(
        messages: $messages,
        model: Model::small->value,
        maxTokens: 300,
    );

    $streamedContent = '';

    foreach ($stream as $chunk) {
        if ($chunkCount === 0) {
            $firstChunkTime = microtime(true) - $start;
            echo '✅ First chunk received in '.number_format($firstChunkTime, 3)." seconds\n";
        }

        $choice = $chunk->choices->first();
        if (! $choice) {
            continue;
        }

        if (isset($choice->delta->content)) {
            $streamedContent .= $choice->delta->content;
            $chunkCount++;
        }
    }

    $streamTime = microtime(true) - $start;

    echo "✅ Stream completed\n";
    if ($firstChunkTime !== null) {
        echo '  • Time to first chunk: '.number_format($firstChunkTime, 3)." seconds\n";
    }
    echo '  • Total time: '.number_format($streamTime, 3)." seconds\n";
    echo '  • Content length: '.strlen($streamedContent)." characters\n";
    echo "  • Total chunks: {$chunkCount}\n\n";

    // Comparison
    echo "📊 Performance Comparison:\n";
    echo str_repeat('─', 40)."\n";
    echo 'Non-streaming total time: '.number_format($nonStreamTime, 3)." seconds\n";
    echo 'Streaming total time: '.number_format($streamTime, 3)." seconds\n";
    echo 'Streaming first response: '.number_format($firstChunkTime, 3)." seconds ⚡\n\n";

    $improvement = (1 - ($firstChunkTime / $nonStreamTime)) * 100;
    echo '🚀 Streaming provides ~'.round($improvement)."% faster initial response!\n\n";

    echo "💡 When to Use Streaming:\n";
    echo "  ✅ Interactive chatbots\n";
    echo "  ✅ Long-form content generation\n";
    echo "  ✅ Real-time applications\n";
    echo "  ✅ Better UX for slow responses\n\n";

    echo "💡 When to Use Non-Streaming:\n";
    echo "  ✅ Batch processing\n";
    echo "  ✅ Need complete response before processing\n";
    echo "  ✅ Storing responses to database\n";
    echo "  ✅ API-to-API communication\n\n";
}

/**
 * Example 4: Error handling for streaming responses
 */
function streamingErrorHandling(Mistral $mistral): void
{
    displaySection('Example 4: Streaming Error Handling');
    echo "Implementing robust error handling for streams...\n\n";

    $messages = [
        [
            'role' => Role::user->value,
            'content' => 'Explain the concept of middleware in web applications.',
        ],
    ];

    echo "🌊 Streaming with error handling:\n";
    echo str_repeat('═', 50)."\n";

    try {
        $stream = $mistral->chat()->createStreamed(
            messages: $messages,
            model: Model::small->value,
            maxTokens: 200,
        );

        $fullText = '';
        $chunkCount = 0;
        $lastChunkTime = microtime(true);
        $timeout = 30; // seconds

        foreach ($stream as $chunk) {
            // Check for timeout between chunks
            if (microtime(true) - $lastChunkTime > $timeout) {
                throw new RuntimeException('Stream timeout: No data received for '.$timeout.' seconds');
            }

            $choice = $chunk->choices->first();
            if (! $choice) {
                continue;
            }

            if (isset($choice->delta->content)) {
                $content = $choice->delta->content;
                echo $content;
                flush();

                $fullText .= $content;
                $chunkCount++;
                $lastChunkTime = microtime(true);
            }

            // Handle finish reasons
            if (isset($choice->finishReason)) {
                $finishReason = $choice->finishReason;
                echo "\n\n";

                // Different finish reasons require different handling
                $message = match ($finishReason) {
                    'stop' => "✅ Stream completed normally\n",
                    'length' => "⚠️ Stream stopped: max_tokens limit reached\n",
                    'content_filter' => "⚠️ Stream stopped: content filter triggered\n",
                    'tool_calls' => "✅ Stream completed with tool calls\n",
                    default => "ℹ️ Stream finished: {$finishReason}\n",
                };
                echo $message;
            }
        }

        echo str_repeat('═', 50)."\n\n";
        echo "✅ Streaming completed successfully\n";
        echo "  • Chunks received: {$chunkCount}\n";
        echo '  • Total characters: '.strlen($fullText)."\n\n";

    } catch (RequestTimeOutException|GatewayTimeoutException $e) {
        echo "\n\n❌ Error: {$e->getMessage()}\n";
        echo "  • Increase timeout in client configuration\n";
        echo "  • Reduce maxTokens parameter\n";
        echo "  • Check network connectivity\n\n";
    } catch (FatalRequestException $e) {
        echo "\n\n❌ Stream connection error\n";
        echo "  • Error: {$e->getMessage()}\n";
        echo "  • Implement retry logic for production\n\n";
    } catch (RuntimeException $e) {
        echo "\n\n❌ Stream processing error\n";
        echo "  • Error: {$e->getMessage()}\n\n";
    } catch (Throwable $e) {
        echo "\n\n❌ Unexpected error\n";
        echo "  • Error: {$e->getMessage()}\n\n";
    }

    echo "💡 Streaming Error Handling Best Practices:\n";
    echo "  • Always use try-catch around streams\n";
    echo "  • Monitor time between chunks\n";
    echo "  • Handle network interruptions gracefully\n";
    echo "  • Check finish_reason for completion status\n";
    echo "  • Implement retry logic for production\n";
    echo "  • Buffer partial responses for recovery\n";
    echo "  • Log streaming errors for debugging\n\n";
}

// Run the example if executed directly
if (php_sapi_name() === 'cli') {
    main();
}
