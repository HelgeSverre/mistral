# Streaming Chat Completions with Mistral PHP SDK

## Overview

This example demonstrates how to implement streaming responses using Server-Sent Events (SSE) with the Mistral PHP SDK.
Streaming allows you to receive and process AI responses in real-time as they're generated, creating a more responsive
and interactive user experience similar to ChatGPT's typewriter effect.

### Real-world Use Cases

- Interactive chatbots with real-time responses
- Live content generation for web applications
- Progressive document creation
- Real-time translation services
- Responsive coding assistants

### Prerequisites

- Completed [03-chat-parameters](../03-chat-parameters) example
- Understanding of PHP generators and streams
- Basic knowledge of Server-Sent Events (SSE)
- Familiarity with asynchronous processing concepts

## Concepts

### Streaming vs Standard Responses

**Standard Response**: Wait for complete generation, then return all at once

- Pros: Simple to implement, easy error handling
- Cons: Long wait times, no intermediate feedback

**Streaming Response**: Receive tokens as they're generated

- Pros: Immediate feedback, better UX, ability to cancel mid-stream
- Cons: More complex implementation, requires special handling

### Server-Sent Events (SSE)

SSE is a standard for pushing data from server to client over HTTP:

- One-way communication (server to client)
- Automatic reconnection
- Simple text-based protocol
- Native browser support

### Token Streaming

Mistral generates text token by token. With streaming:

1. Connection established with `stream: true`
2. Tokens sent as `data: ` events
3. Final event signals completion
4. Connection closes

## Implementation

### Basic Streaming Setup

Enable streaming in your chat request:

```php
<?php

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Chat\ChatCompletionRequest;
use Helge\Mistral\Dto\Chat\ChatMessage;
use Helge\Mistral\Enums\Role;

$mistral = new Mistral($_ENV['MISTRAL_API_KEY']);

$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => 'Write a short story about a robot learning to paint.',
        ]),
    ],
    'stream' => true, // Enable streaming
    'temperature' => 0.8,
]);

// Get streaming response
$stream = $mistral->chat()->createStreamed($request);

// Process tokens as they arrive
foreach ($stream as $response) {
    if (isset($response->choices[0]->delta->content)) {
        echo $response->choices[0]->delta->content;
        flush(); // Send to browser immediately
    }
}
```

### Advanced Streaming Handler

Build a robust streaming handler with error handling:

```php
class StreamHandler
{
    private string $buffer = '';
    private array $metrics = [
        'tokens' => 0,
        'startTime' => null,
        'endTime' => null,
    ];

    public function processStream($stream, callable $onToken = null): string
    {
        $this->metrics['startTime'] = microtime(true);
        $fullContent = '';

        try {
            foreach ($stream as $chunk) {
                // Extract content from delta
                $content = $chunk->choices[0]->delta->content ?? '';

                if ($content) {
                    $fullContent .= $content;
                    $this->metrics['tokens']++;

                    // Call token handler if provided
                    if ($onToken) {
                        $onToken($content, $this->metrics);
                    }
                }

                // Check for finish reason
                if (isset($chunk->choices[0]->finishReason)) {
                    break;
                }
            }
        } catch (Exception $e) {
            throw new Exception("Stream processing error: " . $e->getMessage());
        } finally {
            $this->metrics['endTime'] = microtime(true);
        }

        return $fullContent;
    }

    public function getMetrics(): array
    {
        $duration = $this->metrics['endTime'] - $this->metrics['startTime'];
        return [
            'tokens' => $this->metrics['tokens'],
            'duration' => round($duration, 2),
            'tokensPerSecond' => round($this->metrics['tokens'] / $duration, 2),
        ];
    }
}
```

### Web-Based Streaming Interface

Create a web interface for streaming responses:

```php
// stream-endpoint.php
<?php

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no'); // Disable Nginx buffering

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Chat\ChatCompletionRequest;
use Helge\Mistral\Dto\Chat\ChatMessage;
use Helge\Mistral\Enums\Role;

$userMessage = $_GET['message'] ?? 'Hello!';
$mistral = new Mistral($_ENV['MISTRAL_API_KEY']);

$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => $userMessage,
        ]),
    ],
    'stream' => true,
]);

try {
    $stream = $mistral->chat()->createStreamed($request);

    foreach ($stream as $chunk) {
        $content = $chunk->choices[0]->delta->content ?? '';

        if ($content) {
            // Send as SSE event
            echo "data: " . json_encode(['content' => $content]) . "\n\n";
            flush();
        }

        // Check if generation is complete
        if (isset($chunk->choices[0]->finishReason)) {
            echo "data: " . json_encode(['done' => true]) . "\n\n";
            break;
        }
    }
} catch (Exception $e) {
    echo "data: " . json_encode(['error' => $e->getMessage()]) . "\n\n";
}
```

### Client-Side JavaScript

Handle SSE on the frontend:

```html
<!DOCTYPE html>
<html>
    <head>
        <title>Mistral Streaming Chat</title>
    </head>
    <body>
        <div id="chat-container">
            <div id="messages"></div>
            <input
                type="text"
                id="user-input"
                placeholder="Type your message..."
            />
            <button onclick="sendMessage()">Send</button>
        </div>

        <script>
            function sendMessage() {
                const input = document.getElementById("user-input");
                const message = input.value;
                input.value = "";

                // Display user message
                const messagesDiv = document.getElementById("messages");
                messagesDiv.innerHTML += `<div class="user">You: ${message}</div>`;

                // Create response container
                const responseDiv = document.createElement("div");
                responseDiv.className = "assistant";
                responseDiv.innerHTML = "Assistant: ";
                messagesDiv.appendChild(responseDiv);

                // Start SSE connection
                const eventSource = new EventSource(
                    `stream-endpoint.php?message=${encodeURIComponent(message)}`,
                );

                eventSource.onmessage = function (event) {
                    const data = JSON.parse(event.data);

                    if (data.content) {
                        responseDiv.innerHTML += data.content;
                    } else if (data.done) {
                        eventSource.close();
                    } else if (data.error) {
                        responseDiv.innerHTML += `<span class="error">Error: ${data.error}</span>`;
                        eventSource.close();
                    }
                };

                eventSource.onerror = function () {
                    eventSource.close();
                };
            }
        </script>
    </body>
</html>
```

## Code Example

Complete working example (`streaming-chat.php`):

```php
<?php

require_once 'vendor/autoload.php';

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Chat\ChatCompletionRequest;
use Helge\Mistral\Dto\Chat\ChatMessage;
use Helge\Mistral\Enums\Role;

$apiKey = $_ENV['MISTRAL_API_KEY'] ?? getenv('MISTRAL_API_KEY');
if (!$apiKey) {
    die("Please set your MISTRAL_API_KEY environment variable\n");
}

$mistral = new Mistral($apiKey);

// Example 1: Basic Streaming
echo "=== Example 1: Basic Streaming ===\n\n";
echo "Generating story with streaming...\n\n";

$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::System,
            'content' => 'You are a creative writer. Keep responses brief.',
        ]),
        ChatMessage::from([
            'role' => Role::User,
            'content' => 'Write a haiku about PHP programming.',
        ]),
    ],
    'stream' => true,
    'temperature' => 0.8,
]);

$startTime = microtime(true);
$fullResponse = '';
$tokenCount = 0;

try {
    $stream = $mistral->chat()->createStreamed($request);

    foreach ($stream as $chunk) {
        if (isset($chunk->choices[0]->delta->content)) {
            $token = $chunk->choices[0]->delta->content;
            echo $token; // Display token immediately
            $fullResponse .= $token;
            $tokenCount++;

            // Simulate typewriter effect in CLI
            usleep(30000); // 30ms delay
        }
    }
} catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
}

$duration = microtime(true) - $startTime;
echo "\n\n--- Streaming Metrics ---\n";
echo "Tokens: {$tokenCount}\n";
echo "Duration: " . round($duration, 2) . "s\n";
echo "Tokens/sec: " . round($tokenCount / $duration, 2) . "\n\n";

// Example 2: Streaming with Progress Indicator
echo "=== Example 2: Streaming with Progress ===\n\n";

class ProgressStream
{
    private int $charCount = 0;
    private int $lineLength = 0;
    private const MAX_LINE = 80;

    public function processToken(string $token): void
    {
        echo $token;
        $this->charCount += mb_strlen($token);
        $this->lineLength += mb_strlen($token);

        // Word wrap
        if ($this->lineLength > self::MAX_LINE && $token === ' ') {
            echo "\n";
            $this->lineLength = 0;
        }
    }

    public function showProgress(): void
    {
        echo "\r[Generating... {$this->charCount} characters]";
    }
}

$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => 'Explain streaming in 3 sentences.',
        ]),
    ],
    'stream' => true,
]);

$progress = new ProgressStream();
$stream = $mistral->chat()->createStreamed($request);

echo "Response: ";
foreach ($stream as $chunk) {
    if (isset($chunk->choices[0]->delta->content)) {
        $progress->processToken($chunk->choices[0]->delta->content);
    }
}
echo "\n\n";

// Example 3: Streaming with Buffer Management
echo "=== Example 3: Buffered Streaming ===\n\n";

class BufferedStream
{
    private string $buffer = '';
    private int $bufferSize;
    private callable $onFlush;

    public function __construct(int $bufferSize = 10, callable $onFlush = null)
    {
        $this->bufferSize = $bufferSize;
        $this->onFlush = $onFlush ?? function($content) {
            echo $content;
        };
    }

    public function write(string $content): void
    {
        $this->buffer .= $content;

        // Flush when buffer exceeds size
        if (mb_strlen($this->buffer) >= $this->bufferSize) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        if ($this->buffer) {
            ($this->onFlush)($this->buffer);
            $this->buffer = '';
        }
    }
}

$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => 'Count from 1 to 10 with descriptions.',
        ]),
    ],
    'stream' => true,
]);

$buffered = new BufferedStream(20, function($content) {
    echo "[BUFFER: " . strlen($content) . " chars] " . $content . "\n";
    usleep(100000); // Simulate processing delay
});

$stream = $mistral->chat()->createStreamed($request);
foreach ($stream as $chunk) {
    if (isset($chunk->choices[0]->delta->content)) {
        $buffered->write($chunk->choices[0]->delta->content);
    }
}
$buffered->flush(); // Don't forget final flush!

echo "\n";

// Example 4: Cancellable Streaming
echo "=== Example 4: Cancellable Streaming ===\n\n";

class CancellableStream
{
    private bool $cancelled = false;
    private string $collected = '';

    public function cancel(): void
    {
        $this->cancelled = true;
    }

    public function process($stream, int $maxTokens = 50): string
    {
        $tokenCount = 0;

        foreach ($stream as $chunk) {
            if ($this->cancelled) {
                echo "\n[Stream cancelled by user]\n";
                break;
            }

            if (isset($chunk->choices[0]->delta->content)) {
                $content = $chunk->choices[0]->delta->content;
                echo $content;
                $this->collected .= $content;
                $tokenCount++;

                // Auto-cancel after max tokens (for demo)
                if ($tokenCount >= $maxTokens) {
                    $this->cancel();
                }
            }
        }

        return $this->collected;
    }
}

$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => 'Write a long story about space exploration.',
        ]),
    ],
    'stream' => true,
    'maxTokens' => 500,
]);

echo "Starting cancellable stream (will stop after 50 tokens)...\n\n";

$cancellable = new CancellableStream();
$stream = $mistral->chat()->createStreamed($request);
$result = $cancellable->process($stream, 50);

echo "\n\nCollected " . str_word_count($result) . " words before cancellation.\n";

// Example 5: Streaming with Multiple Handlers
echo "\n=== Example 5: Multi-Handler Streaming ===\n\n";

class MultiHandlerStream
{
    private array $handlers = [];

    public function addHandler(string $name, callable $handler): void
    {
        $this->handlers[$name] = $handler;
    }

    public function process($stream): array
    {
        $results = [];

        foreach ($stream as $chunk) {
            if (isset($chunk->choices[0]->delta->content)) {
                $content = $chunk->choices[0]->delta->content;

                // Call all handlers
                foreach ($this->handlers as $name => $handler) {
                    $results[$name] = $handler($content, $results[$name] ?? null);
                }
            }
        }

        return $results;
    }
}

$multiHandler = new MultiHandlerStream();

// Add different handlers
$multiHandler->addHandler('display', function($token, $prev) {
    echo $token;
    return ($prev ?? '') . $token;
});

$multiHandler->addHandler('wordCount', function($token, $prev) {
    $count = $prev ?? 0;
    if (preg_match('/\s+/', $token)) {
        $count++;
    }
    return $count;
});

$multiHandler->addHandler('charCount', function($token, $prev) {
    return ($prev ?? 0) + mb_strlen($token);
});

$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => 'Describe PHP in one paragraph.',
        ]),
    ],
    'stream' => true,
]);

$stream = $mistral->chat()->createStreamed($request);
$results = $multiHandler->process($stream);

echo "\n\n--- Multi-Handler Results ---\n";
echo "Words: " . ($results['wordCount'] ?? 0) . "\n";
echo "Characters: " . ($results['charCount'] ?? 0) . "\n";
```

## Expected Output

You'll see real-time token streaming:

```
=== Example 1: Basic Streaming ===

Generating story with streaming...

Code flows like art,
Brackets dance in perfect sync,
PHP creates.

--- Streaming Metrics ---
Tokens: 17
Duration: 1.54s
Tokens/sec: 11.04

=== Example 2: Streaming with Progress ===

Response: Streaming allows data to be transmitted and processed in real-time as it's
generated, rather than waiting for the complete response. This creates a more
responsive user experience. It's particularly useful for large responses or when
immediate feedback is important.

[Progress indicators and buffered output examples follow...]
```

## Try It Yourself

### Exercise 1: Build a Chat Interface

Create a complete streaming chat interface:

```php
class StreamingChat {
    private array $history = [];
    private Mistral $client;

    public function streamResponse(string $message): Generator
    {
        $this->history[] = ['role' => 'user', 'content' => $message];

        $request = ChatCompletionRequest::from([
            'model' => 'mistral-small-latest',
            'messages' => $this->history,
            'stream' => true,
        ]);

        $fullResponse = '';
        $stream = $this->client->chat()->createStreamed($request);

        foreach ($stream as $chunk) {
            if (isset($chunk->choices[0]->delta->content)) {
                $token = $chunk->choices[0]->delta->content;
                $fullResponse .= $token;
                yield $token;
            }
        }

        $this->history[] = ['role' => 'assistant', 'content' => $fullResponse];
    }
}
```

### Exercise 2: Add Stream Analytics

Track streaming performance:

```php
class StreamAnalytics {
    public function analyze($stream): array {
        $firstTokenTime = null;
        $metrics = [
            'firstTokenLatency' => 0,
            'totalTokens' => 0,
            'totalTime' => 0,
        ];

        $startTime = microtime(true);
        foreach ($stream as $chunk) {
            if (!$firstTokenTime && isset($chunk->choices[0]->delta->content)) {
                $firstTokenTime = microtime(true);
                $metrics['firstTokenLatency'] = $firstTokenTime - $startTime;
            }
            // Continue tracking...
        }
        return $metrics;
    }
}
```

### Exercise 3: Implement Stream Transformation

Transform tokens as they arrive:

```php
function streamWithMarkdown($stream): Generator {
    foreach ($stream as $chunk) {
        if (isset($chunk->choices[0]->delta->content)) {
            $content = $chunk->choices[0]->delta->content;
            // Convert markdown to HTML on the fly
            yield parseMarkdown($content);
        }
    }
}
```

## Troubleshooting

### Issue: No Streaming Output Visible

- **Solution**: Call `flush()` after each token
- Disable output buffering: `ob_implicit_flush(true)`
- Check web server buffering settings

### Issue: Connection Timeouts

- **Solution**: Set appropriate timeouts in PHP and web server
- Use `set_time_limit(0)` for long streams
- Implement heartbeat/keepalive messages

### Issue: Memory Issues with Long Streams

- **Solution**: Process tokens immediately, don't buffer all
- Use generators to maintain low memory footprint
- Implement chunked processing

### Issue: Browser Not Showing SSE

- **Solution**: Ensure correct headers are set
- Check for proxy/CDN buffering
- Verify Content-Type is `text/event-stream`

## Next Steps

Master more advanced features:

1. **[05-function-calling](../05-function-calling)**: Combine streaming with function calls
2. **[10-error-handling](../10-error-handling)**: Handle streaming errors gracefully
3. **[02-basic-chat](../02-basic-chat)**: Review chat fundamentals

### Further Reading

- [Server-Sent Events Specification](https://html.spec.whatwg.org/multipage/server-sent-events.html)
- [PHP Streams Documentation](https://www.php.net/manual/en/book.stream.php)
- [Mistral Streaming API](https://docs.mistral.ai/api/#operation/createChatCompletion)

### Production Considerations

- **Connection Management**: Implement proper cleanup for dropped connections
- **Rate Limiting**: Respect API rate limits even with streaming
- **Error Recovery**: Implement reconnection logic for failed streams
- **Security**: Validate and sanitize streamed content before display
- **Monitoring**: Track streaming metrics for performance optimization

Remember: Streaming provides better UX but requires careful implementation. Always test with various network conditions
and client configurations!
