# Robust Error Handling with Mistral PHP SDK

## Overview

This example demonstrates comprehensive error handling strategies for the Mistral PHP SDK. You'll learn how to
gracefully handle API errors, implement retry logic, manage rate limits, and build resilient applications that can
recover from failures without disrupting user experience.

### Real-world Use Cases

- Production API integration
- High-availability applications
- Rate-limited environments
- Network-unstable conditions
- Cost-controlled deployments
- Graceful degradation strategies

### Prerequisites

- Completed previous examples (01-09)
- Understanding of PHP exceptions
- Knowledge of HTTP status codes
- Familiarity with exponential backoff

## Concepts

### Error Categories

Common errors when using Mistral API:

- **Authentication Errors** (401): Invalid or expired API key
- **Rate Limiting** (429): Too many requests
- **Bad Request** (400): Invalid parameters
- **Server Errors** (500-503): Temporary API issues
- **Network Errors**: Connection timeouts, DNS failures
- **Validation Errors**: Invalid input data

### Recovery Strategies

- **Retry Logic**: Automatic retry with backoff
- **Fallback Methods**: Alternative approaches when primary fails
- **Circuit Breakers**: Prevent cascading failures
- **Graceful Degradation**: Reduced functionality vs complete failure
- **Error Logging**: Comprehensive logging for debugging

### Best Practices

- Always catch specific exceptions
- Implement exponential backoff
- Respect rate limit headers
- Log errors with context
- Provide user-friendly error messages

## Implementation

### Basic Error Handling

Handle common API errors:

```php
<?php

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Chat\ChatCompletionRequest;
use Helge\Mistral\Dto\Chat\ChatMessage;
use Helge\Mistral\Enums\Role;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\Statuses\UnauthorizedException;
use Saloon\Exceptions\Request\Statuses\TooManyRequestsException;

function safeApiCall(Mistral $client, ChatCompletionRequest $request): ?string
{
    try {
        $dto = $client->chat()->createDto($request);
        return $dto->choices[0]->message->content;

    } catch (UnauthorizedException $e) {
        error_log("Authentication failed: Check your API key");
        return null;

    } catch (TooManyRequestsException $e) {
        $retryAfter = $e->response->header('Retry-After') ?? 60;
        error_log("Rate limited. Retry after {$retryAfter} seconds");
        return null;

    } catch (RequestException $e) {
        $status = $e->getStatus();
        $message = $e->getMessage();
        error_log("API error ({$status}): {$message}");
        return null;

    } catch (Exception $e) {
        error_log("Unexpected error: " . $e->getMessage());
        return null;
    }
}
```

### Advanced Error Handler with Retry

Implement sophisticated retry logic:

```php
class ResilientMistralClient
{
    private Mistral $client;
    private int $maxRetries = 3;
    private int $baseDelay = 1000; // milliseconds
    private array $errorLog = [];

    public function __construct(string $apiKey)
    {
        $this->client = new Mistral($apiKey);
    }

    public function request(callable $operation, array $context = []): mixed
    {
        $lastException = null;
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                // Execute the operation
                $result = $operation($this->client);

                // Success - reset error count
                if ($attempt > 0) {
                    $this->logRecovery($context, $attempt);
                }

                return $result;

            } catch (TooManyRequestsException $e) {
                $lastException = $e;
                $delay = $this->handleRateLimit($e, $attempt);
                $this->logError('rate_limit', $e, $context);

                if ($attempt < $this->maxRetries - 1) {
                    usleep($delay * 1000);
                }

            } catch (RequestException $e) {
                $lastException = $e;
                $status = $e->getStatus();

                // Don't retry client errors (4xx except 429)
                if ($status >= 400 && $status < 500 && $status !== 429) {
                    $this->logError('client_error', $e, $context);
                    throw $e;
                }

                // Retry server errors (5xx)
                if ($status >= 500) {
                    $delay = $this->calculateBackoff($attempt);
                    $this->logError('server_error', $e, $context);

                    if ($attempt < $this->maxRetries - 1) {
                        usleep($delay * 1000);
                    }
                }

            } catch (Exception $e) {
                $lastException = $e;
                $this->logError('unexpected', $e, $context);

                // Network errors - retry with backoff
                if ($this->isNetworkError($e)) {
                    $delay = $this->calculateBackoff($attempt);

                    if ($attempt < $this->maxRetries - 1) {
                        usleep($delay * 1000);
                    }
                } else {
                    // Non-recoverable error
                    throw $e;
                }
            }

            $attempt++;
        }

        // Max retries exceeded
        $this->logMaxRetriesExceeded($context, $lastException);
        throw new Exception(
            "Max retries ({$this->maxRetries}) exceeded. Last error: " .
            $lastException->getMessage(),
            0,
            $lastException
        );
    }

    private function calculateBackoff(int $attempt): int
    {
        // Exponential backoff with jitter
        $exponentialDelay = $this->baseDelay * pow(2, $attempt);
        $jitter = rand(0, 1000);

        return min($exponentialDelay + $jitter, 30000); // Max 30 seconds
    }

    private function handleRateLimit(TooManyRequestsException $e, int $attempt): int
    {
        $retryAfter = $e->response->header('Retry-After');

        if ($retryAfter) {
            // Use server-specified delay
            return (int)$retryAfter * 1000;
        }

        // Fallback to exponential backoff
        return $this->calculateBackoff($attempt);
    }

    private function isNetworkError(Exception $e): bool
    {
        $networkErrors = [
            'Connection timed out',
            'Could not resolve host',
            'Network is unreachable',
            'Connection refused',
        ];

        foreach ($networkErrors as $error) {
            if (stripos($e->getMessage(), $error) !== false) {
                return true;
            }
        }

        return false;
    }

    private function logError(string $type, Exception $e, array $context): void
    {
        $this->errorLog[] = [
            'type' => $type,
            'message' => $e->getMessage(),
            'context' => $context,
            'timestamp' => time(),
        ];

        error_log(sprintf(
            "[%s] Error type: %s, Message: %s, Context: %s",
            date('Y-m-d H:i:s'),
            $type,
            $e->getMessage(),
            json_encode($context)
        ));
    }

    private function logRecovery(array $context, int $attempts): void
    {
        error_log(sprintf(
            "[%s] Recovered after %d attempts. Context: %s",
            date('Y-m-d H:i:s'),
            $attempts,
            json_encode($context)
        ));
    }

    private function logMaxRetriesExceeded(array $context, ?Exception $lastException): void
    {
        error_log(sprintf(
            "[%s] Max retries exceeded. Context: %s, Last error: %s",
            date('Y-m-d H:i:s'),
            json_encode($context),
            $lastException ? $lastException->getMessage() : 'Unknown'
        ));
    }

    public function getErrorLog(): array
    {
        return $this->errorLog;
    }
}
```

### Circuit Breaker Pattern

Prevent cascading failures:

```php
class CircuitBreaker
{
    private string $name;
    private int $failureThreshold;
    private int $recoveryTimeout;
    private int $failureCount = 0;
    private ?int $lastFailureTime = null;
    private string $state = 'closed'; // closed, open, half-open

    public function __construct(
        string $name,
        int $failureThreshold = 5,
        int $recoveryTimeout = 60
    ) {
        $this->name = $name;
        $this->failureThreshold = $failureThreshold;
        $this->recoveryTimeout = $recoveryTimeout;
    }

    public function call(callable $operation): mixed
    {
        if (!$this->canAttempt()) {
            throw new Exception("Circuit breaker '{$this->name}' is open");
        }

        try {
            $result = $operation();
            $this->onSuccess();
            return $result;

        } catch (Exception $e) {
            $this->onFailure();
            throw $e;
        }
    }

    private function canAttempt(): bool
    {
        if ($this->state === 'closed') {
            return true;
        }

        if ($this->state === 'open') {
            // Check if recovery timeout has passed
            if (time() - $this->lastFailureTime >= $this->recoveryTimeout) {
                $this->state = 'half-open';
                return true;
            }
            return false;
        }

        // Half-open state - allow one attempt
        return true;
    }

    private function onSuccess(): void
    {
        if ($this->state === 'half-open') {
            // Recovery successful
            $this->state = 'closed';
            $this->failureCount = 0;
            $this->lastFailureTime = null;
        }
    }

    private function onFailure(): void
    {
        $this->failureCount++;
        $this->lastFailureTime = time();

        if ($this->failureCount >= $this->failureThreshold) {
            $this->state = 'open';
        }
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function reset(): void
    {
        $this->state = 'closed';
        $this->failureCount = 0;
        $this->lastFailureTime = null;
    }
}
```

### Fallback Strategies

Implement graceful degradation:

```php
class FallbackHandler
{
    private array $strategies = [];

    public function addStrategy(string $name, callable $strategy, int $priority = 0): void
    {
        $this->strategies[] = [
            'name' => $name,
            'strategy' => $strategy,
            'priority' => $priority,
        ];

        // Sort by priority
        usort($this->strategies, fn($a, $b) => $b['priority'] - $a['priority']);
    }

    public function execute(array $context = []): mixed
    {
        $errors = [];

        foreach ($this->strategies as $strategy) {
            try {
                $result = ($strategy['strategy'])($context);

                if ($result !== null) {
                    $this->logSuccess($strategy['name'], $context);
                    return $result;
                }

            } catch (Exception $e) {
                $errors[$strategy['name']] = $e->getMessage();
                $this->logFailure($strategy['name'], $e, $context);
            }
        }

        throw new Exception(
            "All fallback strategies failed: " . json_encode($errors)
        );
    }

    private function logSuccess(string $strategy, array $context): void
    {
        error_log("Fallback strategy '{$strategy}' succeeded");
    }

    private function logFailure(string $strategy, Exception $e, array $context): void
    {
        error_log("Fallback strategy '{$strategy}' failed: " . $e->getMessage());
    }
}
```

## Code Example

Complete working example (`error-handling.php`):

```php
<?php

require_once 'vendor/autoload.php';

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Chat\ChatCompletionRequest;
use Helge\Mistral\Dto\Chat\ChatMessage;
use Helge\Mistral\Enums\Role;
use Saloon\Exceptions\Request\RequestException;

$apiKey = $_ENV['MISTRAL_API_KEY'] ?? getenv('MISTRAL_API_KEY');
if (!$apiKey) {
    die("Please set your MISTRAL_API_KEY environment variable\n");
}

// Example 1: Basic Error Handling
echo "=== Example 1: Basic Error Handling ===\n\n";

function handleBasicErrors(string $apiKey): void
{
    $client = new Mistral($apiKey);

    $testCases = [
        [
            'name' => 'Valid Request',
            'request' => ChatCompletionRequest::from([
                'model' => 'mistral-small-latest',
                'messages' => [
                    ChatMessage::from([
                        'role' => Role::User,
                        'content' => 'Hello!',
                    ]),
                ],
                'maxTokens' => 10,
            ]),
        ],
        [
            'name' => 'Invalid Model',
            'request' => ChatCompletionRequest::from([
                'model' => 'invalid-model-name',
                'messages' => [
                    ChatMessage::from([
                        'role' => Role::User,
                        'content' => 'Hello!',
                    ]),
                ],
            ]),
        ],
    ];

    foreach ($testCases as $test) {
        echo "Testing: {$test['name']}\n";

        try {
            $dto = $client->chat()->createDto($test['request']);
            echo "  ✓ Success: " . substr($dto->choices[0]->message->content, 0, 50) . "\n";

        } catch (RequestException $e) {
            $status = $e->getStatus();
            echo "  ✗ API Error ({$status}): " . $e->getMessage() . "\n";

        } catch (Exception $e) {
            echo "  ✗ Unexpected Error: " . $e->getMessage() . "\n";
        }

        echo "\n";
    }
}

handleBasicErrors($apiKey);

// Example 2: Retry with Exponential Backoff
echo "=== Example 2: Retry with Exponential Backoff ===\n\n";

class RetryClient
{
    private Mistral $client;
    private int $maxRetries = 3;

    public function __construct(string $apiKey)
    {
        $this->client = new Mistral($apiKey);
    }

    public function createChatCompletion(ChatCompletionRequest $request): ?object
    {
        $attempt = 0;
        $baseDelay = 1; // seconds

        while ($attempt < $this->maxRetries) {
            try {
                echo "  Attempt " . ($attempt + 1) . "/{$this->maxRetries}...";
                $dto = $this->client->chat()->createDto($request);
                echo " Success!\n";
                return $dto;

            } catch (RequestException $e) {
                $status = $e->getStatus();

                // Don't retry client errors (except rate limits)
                if ($status >= 400 && $status < 500 && $status !== 429) {
                    echo " Failed (non-retryable): {$status}\n";
                    throw $e;
                }

                // Calculate delay with exponential backoff
                $delay = $baseDelay * pow(2, $attempt);
                echo " Failed (retryable). Waiting {$delay}s...\n";

                if ($attempt < $this->maxRetries - 1) {
                    sleep($delay);
                }

            } catch (Exception $e) {
                echo " Failed (unexpected): " . $e->getMessage() . "\n";
                throw $e;
            }

            $attempt++;
        }

        echo "  All retries exhausted.\n";
        return null;
    }
}

$retryClient = new RetryClient($apiKey);

$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => 'Test retry logic',
        ]),
    ],
    'maxTokens' => 10,
]);

echo "Testing retry logic:\n";
try {
    $dto = $retryClient->createChatCompletion($request);
    if ($dto) {
        echo "Final result: " . $dto->choices[0]->message->content . "\n";
    }
} catch (Exception $e) {
    echo "Failed after retries: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 3: Rate Limit Handling
echo "=== Example 3: Rate Limit Handling ===\n\n";

class RateLimitHandler
{
    private Mistral $client;
    private int $requestCount = 0;
    private float $lastRequestTime = 0;
    private int $maxRequestsPerMinute = 60;

    public function __construct(string $apiKey)
    {
        $this->client = new Mistral($apiKey);
    }

    public function request(ChatCompletionRequest $request): object
    {
        $this->enforceRateLimit();

        try {
            $dto = $this->client->chat()->createDto($request);
            $this->requestCount++;
            $this->lastRequestTime = microtime(true);
            return $dto;

        } catch (RequestException $e) {
            if ($e->getStatus() === 429) {
                $retryAfter = $e->response->header('Retry-After') ?? 60;
                echo "  Rate limited. Waiting {$retryAfter} seconds...\n";
                sleep($retryAfter);
                return $this->request($request); // Retry
            }
            throw $e;
        }
    }

    private function enforceRateLimit(): void
    {
        $minInterval = 60 / $this->maxRequestsPerMinute;
        $timeSinceLastRequest = microtime(true) - $this->lastRequestTime;

        if ($timeSinceLastRequest < $minInterval) {
            $waitTime = $minInterval - $timeSinceLastRequest;
            echo "  Rate limiting: waiting " . round($waitTime, 2) . "s\n";
            usleep((int)($waitTime * 1000000));
        }
    }

    public function getStats(): array
    {
        return [
            'requests_made' => $this->requestCount,
            'last_request' => date('Y-m-d H:i:s', (int)$this->lastRequestTime),
        ];
    }
}

$rateLimitHandler = new RateLimitHandler($apiKey);

echo "Testing rate limit handling:\n";
for ($i = 1; $i <= 3; $i++) {
    echo "Request {$i}: ";

    $request = ChatCompletionRequest::from([
        'model' => 'mistral-small-latest',
        'messages' => [
            ChatMessage::from([
                'role' => Role::User,
                'content' => "Request {$i}",
            ]),
        ],
        'maxTokens' => 5,
    ]);

    try {
        $dto = $rateLimitHandler->request($request);
        echo "Success\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

$stats = $rateLimitHandler->getStats();
echo "Stats: " . json_encode($stats) . "\n\n";

// Example 4: Circuit Breaker
echo "=== Example 4: Circuit Breaker Pattern ===\n\n";

$circuitBreaker = new CircuitBreaker('mistral-api', 3, 10);
$client = new Mistral($apiKey);

echo "Testing circuit breaker:\n";

for ($i = 1; $i <= 5; $i++) {
    echo "Request {$i}: ";
    echo "Circuit state: {$circuitBreaker->getState()} - ";

    try {
        $result = $circuitBreaker->call(function() use ($client, $i) {
            // Simulate failures for testing
            if ($i <= 3 && rand(0, 1) === 0) {
                throw new Exception("Simulated failure");
            }

            $request = ChatCompletionRequest::from([
                'model' => 'mistral-small-latest',
                'messages' => [
                    ChatMessage::from([
                        'role' => Role::User,
                        'content' => "Test {$i}",
                    ]),
                ],
                'maxTokens' => 5,
            ]);

            return $client->chat()->createDto($request);
        });

        echo "Success\n";

    } catch (Exception $e) {
        echo "Failed: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Example 5: Fallback Strategies
echo "=== Example 5: Fallback Strategies ===\n\n";

$fallbackHandler = new FallbackHandler();

// Primary strategy: Use powerful model
$fallbackHandler->addStrategy('primary', function($context) use ($client) {
    echo "  Trying primary (mistral-large)...\n";

    $request = ChatCompletionRequest::from([
        'model' => 'mistral-large-latest',
        'messages' => $context['messages'],
        'maxTokens' => 100,
    ]);

    return $client->chat()->createDto($request);
}, 100);

// Fallback 1: Use smaller model
$fallbackHandler->addStrategy('fallback1', function($context) use ($client) {
    echo "  Trying fallback 1 (mistral-small)...\n";

    $request = ChatCompletionRequest::from([
        'model' => 'mistral-small-latest',
        'messages' => $context['messages'],
        'maxTokens' => 50,
    ]);

    return $client->chat()->createDto($request);
}, 50);

// Fallback 2: Return cached/default response
$fallbackHandler->addStrategy('fallback2', function($context) {
    echo "  Trying fallback 2 (cached response)...\n";

    return (object)[
        'choices' => [(object)[
            'message' => (object)[
                'content' => 'Service temporarily unavailable. Please try again later.',
            ],
        ]],
    ];
}, 10);

echo "Testing fallback strategies:\n";

try {
    $result = $fallbackHandler->execute([
        'messages' => [
            ChatMessage::from([
                'role' => Role::User,
                'content' => 'What is the meaning of life?',
            ]),
        ],
    ]);

    echo "Response: " . $result->choices[0]->message->content . "\n";

} catch (Exception $e) {
    echo "All strategies failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 6: Comprehensive Error Logger
echo "=== Example 6: Error Logging and Monitoring ===\n\n";

class ErrorLogger
{
    private string $logFile;
    private array $metrics = [
        'total_requests' => 0,
        'successful' => 0,
        'failed' => 0,
        'errors_by_type' => [],
    ];

    public function __construct(string $logFile = 'mistral_errors.log')
    {
        $this->logFile = $logFile;
    }

    public function logRequest(string $type, bool $success, ?Exception $error = null): void
    {
        $this->metrics['total_requests']++;

        if ($success) {
            $this->metrics['successful']++;
        } else {
            $this->metrics['failed']++;

            if ($error) {
                $errorType = get_class($error);
                $this->metrics['errors_by_type'][$errorType] =
                    ($this->metrics['errors_by_type'][$errorType] ?? 0) + 1;
            }
        }

        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'success' => $success,
            'error' => $error ? [
                'class' => get_class($error),
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
            ] : null,
        ];

        file_put_contents(
            $this->logFile,
            json_encode($logEntry) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }

    public function getMetrics(): array
    {
        $this->metrics['success_rate'] = $this->metrics['total_requests'] > 0
            ? round($this->metrics['successful'] / $this->metrics['total_requests'] * 100, 2)
            : 0;

        return $this->metrics;
    }
}

$errorLogger = new ErrorLogger('/tmp/mistral_test.log');

echo "Testing error logging:\n";

// Simulate various scenarios
$scenarios = [
    ['type' => 'chat', 'success' => true, 'error' => null],
    ['type' => 'chat', 'success' => false, 'error' => new Exception('Network timeout')],
    ['type' => 'embedding', 'success' => true, 'error' => null],
    ['type' => 'moderation', 'success' => false, 'error' => new Exception('Rate limited')],
];

foreach ($scenarios as $scenario) {
    $errorLogger->logRequest(
        $scenario['type'],
        $scenario['success'],
        $scenario['error']
    );

    echo "  Logged: {$scenario['type']} - " .
         ($scenario['success'] ? 'Success' : 'Failed') . "\n";
}

echo "\nMetrics:\n";
$metrics = $errorLogger->getMetrics();
foreach ($metrics as $key => $value) {
    if (is_array($value)) {
        echo "  {$key}:\n";
        foreach ($value as $k => $v) {
            echo "    {$k}: {$v}\n";
        }
    } else {
        echo "  {$key}: {$value}\n";
    }
}

echo "\n=== Summary ===\n";
echo "Error handling best practices:\n";
echo "1. Catch specific exception types\n";
echo "2. Implement retry logic with backoff\n";
echo "3. Respect rate limits\n";
echo "4. Use circuit breakers for stability\n";
echo "5. Provide fallback strategies\n";
echo "6. Log errors comprehensively\n";
echo "7. Monitor and alert on failures\n";
```

## Expected Output

```
=== Example 1: Basic Error Handling ===

Testing: Valid Request
  ✓ Success: Hello! How can I assist you today?

Testing: Invalid Model
  ✗ API Error (404): Model not found

=== Example 2: Retry with Exponential Backoff ===

Testing retry logic:
  Attempt 1/3... Success!
Final result: Test retry logic response

=== Example 3: Rate Limit Handling ===

Request 1: Success
Request 2: Rate limiting: waiting 0.5s
Success
Request 3: Success

=== Example 4: Circuit Breaker Pattern ===

Request 1: Circuit state: closed - Success
Request 2: Circuit state: closed - Failed: Simulated failure
Request 3: Circuit state: closed - Failed: Simulated failure
Request 4: Circuit state: open - Failed: Circuit breaker is open
Request 5: Circuit state: half-open - Success

[Additional examples follow...]
```

## Try It Yourself

### Exercise 1: Build a Retry Pool

Implement connection pooling with retry:

```php
class RetryPool {
    private array $connections = [];
    private int $maxConnections = 5;

    public function getConnection(): Mistral
    {
        // Implement connection pooling with health checks
    }

    public function releaseConnection(Mistral $connection): void
    {
        // Return connection to pool
    }
}
```

### Exercise 2: Implement Cost Controls

Add budget limits to prevent runaway costs:

```php
class CostController {
    private float $budget;
    private float $spent = 0;

    public function canProceed(string $model, int $tokens): bool
    {
        $estimatedCost = $this->estimateCost($model, $tokens);
        return ($this->spent + $estimatedCost) <= $this->budget;
    }
}
```

### Exercise 3: Create Health Checks

Monitor API health proactively:

```php
class HealthMonitor {
    public function checkHealth(): array
    {
        return [
            'api_status' => $this->pingApi(),
            'response_time' => $this->measureLatency(),
            'error_rate' => $this->calculateErrorRate(),
        ];
    }
}
```

## Troubleshooting

### Issue: Frequent 429 Errors

- **Solution**: Implement proper rate limiting
- Use exponential backoff
- Consider upgrading API plan
- Batch requests where possible

### Issue: Timeout Errors

- **Solution**: Increase timeout settings
- Implement retry logic
- Use streaming for long responses
- Check network connectivity

### Issue: Intermittent Failures

- **Solution**: Use circuit breaker pattern
- Implement fallback strategies
- Add comprehensive logging
- Monitor error patterns

### Issue: High Error Rates

- **Solution**: Review error logs for patterns
- Validate inputs before sending
- Implement input sanitization
- Add request validation

## Next Steps

You've completed the foundation examples! Continue with:

- Review all examples for comprehensive understanding
- Build production applications with these patterns
- Explore advanced Mistral features
- Contribute your own examples

### Further Reading

- [Mistral API Error Codes](https://docs.mistral.ai/api/#errors)
- [PHP Exception Handling](https://www.php.net/manual/en/language.exceptions.php)
- [Circuit Breaker Pattern](https://martinfowler.com/bliki/CircuitBreaker.html)
- [Exponential Backoff](https://en.wikipedia.org/wiki/Exponential_backoff)

### Production Checklist

- ✅ Implement comprehensive error handling
- ✅ Add retry logic with backoff
- ✅ Set up monitoring and alerting
- ✅ Create fallback strategies
- ✅ Implement rate limiting
- ✅ Add circuit breakers
- ✅ Set up error logging
- ✅ Test failure scenarios
- ✅ Document error codes
- ✅ Create runbooks for common issues

Remember: Robust error handling is the difference between a prototype and a production-ready application. Always plan
for failure and design systems that can recover gracefully!
