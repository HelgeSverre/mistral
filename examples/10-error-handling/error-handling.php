<?php

/**
 * Error Handling and Resilience
 *
 * Description: Production-ready error handling, retry logic, and resilience patterns
 * Use Case: Building robust applications that handle failures gracefully
 * Prerequisites: MISTRAL_API_KEY in .env file
 *
 * @see https://docs.mistral.ai/api/#errors
 */

declare(strict_types=1);

require_once __DIR__.'/../shared/bootstrap.php';

use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Enums\Role;
use HelgeSverre\Mistral\Mistral;
use Saloon\Exceptions\Request\ClientException;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Exceptions\Request\Statuses\NotFoundException;
use Saloon\Exceptions\Request\Statuses\TooManyRequestsException;
use Saloon\Exceptions\Request\Statuses\UnauthorizedException;
use Saloon\Exceptions\Request\Statuses\UnprocessableEntityException;
use Saloon\Exceptions\Request\TimeoutException;

/**
 * Main execution function
 */
function main(): void
{
    displayTitle('Error Handling and Resilience', '🛡️');

    $mistral = createMistralClient();

    try {
        // Example 1: Common API errors
        commonApiErrors($mistral);

        // Example 2: Retry logic with exponential backoff
        retryLogic($mistral);

        // Example 3: Timeout handling
        timeoutHandling();

        // Example 4: Validation errors
        validationErrors($mistral);

        // Example 5: Circuit breaker pattern
        circuitBreakerPattern($mistral);

    } catch (Throwable $e) {
        handleError($e, exitOnError: false);
    }
}

/**
 * Example 1: Handling common API errors
 */
function commonApiErrors(Mistral $mistral): void
{
    displaySection('Example 1: Common API Errors');
    echo "Understanding and handling different error types...\n\n";

    // Test different error scenarios
    $errorTests = [
        [
            'name' => 'Invalid API Key',
            'description' => 'Using wrong or expired API key',
            'test' => function () {
                $badClient = new Mistral(apiKey: 'invalid_key_12345');
                $badClient->chat()->create(
                    messages: [['role' => 'user', 'content' => 'test']],
                    model: Model::small->value,
                );
            },
            'expected' => UnauthorizedException::class,
        ],
        [
            'name' => 'Invalid Model',
            'description' => 'Using non-existent model name',
            'test' => function () use ($mistral) {
                $mistral->chat()->create(
                    messages: [['role' => 'user', 'content' => 'test']],
                    model: 'non-existent-model',
                );
            },
            'expected' => ClientException::class,
        ],
        [
            'name' => 'Empty Messages',
            'description' => 'Sending empty message array',
            'test' => function () use ($mistral) {
                $mistral->chat()->create(
                    messages: [],
                    model: Model::small->value,
                );
            },
            'expected' => UnprocessableEntityException::class,
        ],
    ];

    foreach ($errorTests as $i => $test) {
        echo 'Test '.($i + 1).": {$test['name']}\n";
        echo str_repeat('─', 60)."\n";
        echo "Description: {$test['description']}\n";
        echo 'Expected error: '.class_basename($test['expected'])."\n\n";

        try {
            $test['test']();
            echo "❌ No error thrown (unexpected)\n\n";
        } catch (Throwable $e) {
            if ($e instanceof $test['expected']) {
                echo "✅ Caught expected error\n";
                echo "Error message: {$e->getMessage()}\n";

                // Handle specific error types (check most specific first)
                if ($e instanceof UnauthorizedException) {
                    echo "💡 Fix: Check your API key in .env file\n";
                } elseif ($e instanceof UnprocessableEntityException) {
                    echo "💡 Fix: Check required fields and data structure\n";
                } elseif ($e instanceof NotFoundException) {
                    echo "💡 Fix: Check resource ID or endpoint\n";
                } elseif ($e instanceof ClientException) {
                    echo "💡 Fix: Validate input parameters before sending\n";
                }
            } else {
                echo '⚠️ Caught unexpected error: '.get_class($e)."\n";
                echo "Message: {$e->getMessage()}\n";
            }
            echo "\n";
        }
    }

    echo "📚 Common HTTP Status Codes:\n";
    echo "  • 400 Bad Request: Invalid parameters\n";
    echo "  • 401 Unauthorized: Invalid API key\n";
    echo "  • 403 Forbidden: Insufficient permissions\n";
    echo "  • 404 Not Found: Resource doesn't exist\n";
    echo "  • 422 Unprocessable Entity: Missing required fields\n";
    echo "  • 429 Too Many Requests: Rate limit exceeded\n";
    echo "  • 500 Internal Server Error: API issue\n";
    echo "  • 503 Service Unavailable: Temporary outage\n\n";
}

/**
 * Example 2: Retry logic with exponential backoff
 */
function retryLogic(Mistral $mistral): void
{
    displaySection('Example 2: Retry Logic');
    echo "Implementing exponential backoff for transient errors...\n\n";

    echo "Scenario: Simulating rate limit and retry\n";
    echo str_repeat('─', 60)."\n\n";

    $maxAttempts = 3;
    $baseDelay = 1; // seconds

    $messages = [
        ['role' => Role::user->value, 'content' => 'Say hello!'],
    ];

    echo "Attempting API call with retry logic...\n";
    echo "Max attempts: {$maxAttempts}\n";
    echo "Base delay: {$baseDelay}s (exponential)\n\n";

    $attempt = 1;
    $success = false;

    while ($attempt <= $maxAttempts && ! $success) {
        try {
            echo "Attempt {$attempt}/{$maxAttempts}...\n";

            $response = $mistral->chat()->create(
                messages: $messages,
                model: Model::small->value,
                maxTokens: 50,
            );

            if ($response->successful()) {
                echo "✅ Success on attempt {$attempt}\n";
                $dto = $response->dtoOrFail();
                echo "Response: {$dto->choices[0]->message->content}\n\n";
                $success = true;
            }

        } catch (TooManyRequestsException $e) {
            echo "⚠️ Rate limit exceeded\n";

            if ($attempt < $maxAttempts) {
                // Calculate delay with exponential backoff
                $delay = $baseDelay * pow(2, $attempt - 1);

                // Add jitter to prevent thundering herd
                $jitter = rand(0, 1000) / 1000; // 0-1 second
                $totalDelay = $delay + $jitter;

                echo "Waiting {$totalDelay}s before retry...\n\n";
                sleep((int) $totalDelay);
                $attempt++;
            } else {
                echo "❌ Max retry attempts exceeded\n\n";
                throw $e;
            }

        } catch (InternalServerErrorException $e) {
            echo "⚠️ Server error (500)\n";

            if ($attempt < $maxAttempts) {
                $delay = $baseDelay * $attempt;
                echo "Retrying in {$delay}s...\n\n";
                sleep($delay);
                $attempt++;
            } else {
                echo "❌ Max retry attempts exceeded\n\n";
                throw $e;
            }

        } catch (Throwable $e) {
            echo '❌ Non-retryable error: '.get_class($e)."\n";
            echo "Message: {$e->getMessage()}\n\n";
            throw $e;
        }
    }

    echo "💡 Retry Best Practices:\n";
    echo "  • Use exponential backoff: 1s, 2s, 4s, 8s...\n";
    echo "  • Add jitter to prevent synchronized retries\n";
    echo "  • Only retry transient errors (429, 500, 503)\n";
    echo "  • Don't retry client errors (400, 401, 404)\n";
    echo "  • Set maximum retry attempts (3-5 typical)\n";
    echo "  • Log all retry attempts for monitoring\n";
    echo "  • Consider circuit breaker for persistent failures\n\n";
}

/**
 * Example 3: Timeout handling
 */
function timeoutHandling(): void
{
    displaySection('Example 3: Timeout Handling');
    echo "Managing request timeouts and long-running operations...\n\n";

    // Create client with custom timeout
    echo "Test 1: Custom timeout configuration\n";
    echo str_repeat('─', 40)."\n";

    try {
        $shortTimeoutClient = new Mistral(
            apiKey: $_ENV['MISTRAL_API_KEY'],
            timeout: 5, // 5 second timeout
        );

        echo "Client timeout: 5 seconds\n";
        echo "Attempting request...\n";

        $messages = [
            ['role' => Role::user->value, 'content' => 'Write a long story...'],
        ];

        $response = $shortTimeoutClient->chat()->create(
            messages: $messages,
            model: Model::small->value,
            maxTokens: 2000, // May take longer than 5s
        );

        $response->dtoOrFail();
        echo "✅ Request completed within timeout\n\n";

    } catch (TimeoutException $e) {
        echo "⚠️ Request timed out\n";
        echo "Message: {$e->getMessage()}\n\n";

        echo "💡 Timeout Solutions:\n";
        echo "  • Increase client timeout\n";
        echo "  • Reduce maxTokens parameter\n";
        echo "  • Use streaming for long responses\n";
        echo "  • Implement async processing\n\n";
    } catch (Throwable $e) {
        echo "❌ Other error: {$e->getMessage()}\n\n";
    }

    echo "Test 2: Timeout strategies\n";
    echo str_repeat('─', 40)."\n\n";

    $strategies = [
        'Short timeout (5s)' => [
            'timeout' => 5,
            'use_case' => 'Quick responses, interactive apps',
            'risk' => 'May timeout on complex queries',
        ],
        'Medium timeout (30s)' => [
            'timeout' => 30,
            'use_case' => 'Standard applications',
            'risk' => 'Balanced trade-off',
        ],
        'Long timeout (60s)' => [
            'timeout' => 60,
            'use_case' => 'Batch processing, analytics',
            'risk' => 'Users may wait too long',
        ],
        'Streaming (no timeout concern)' => [
            'timeout' => 60,
            'use_case' => 'Real-time responses',
            'risk' => 'More complex implementation',
        ],
    ];

    foreach ($strategies as $name => $config) {
        echo "{$name}:\n";
        echo "  • Timeout: {$config['timeout']}s\n";
        echo "  • Use case: {$config['use_case']}\n";
        echo "  • Risk: {$config['risk']}\n\n";
    }

    echo "💡 Timeout Best Practices:\n";
    echo "  • Set timeouts based on use case\n";
    echo "  • Use streaming for long responses\n";
    echo "  • Implement graceful degradation\n";
    echo "  • Show progress indicators to users\n";
    echo "  • Allow users to cancel long requests\n";
    echo "  • Monitor timeout rates in production\n\n";
}

/**
 * Example 4: Validation errors
 */
function validationErrors(Mistral $mistral): void
{
    displaySection('Example 4: Input Validation');
    echo "Validating inputs before sending to API...\n\n";

    echo "Validation checks:\n";
    echo str_repeat('─', 60)."\n\n";

    // Test 1: Validate message structure
    echo "Test 1: Message validation\n";
    $invalidMessages = [
        'missing content',
        ['role' => 'user'], // Missing content
    ];

    try {
        validateMessages($invalidMessages);
        echo "✅ Messages validated\n\n";
    } catch (InvalidArgumentException $e) {
        echo "❌ Validation failed: {$e->getMessage()}\n\n";
    }

    // Test 2: Validate parameters
    echo "Test 2: Parameter validation\n";
    try {
        validateChatParameters(
            temperature: 2.5, // Invalid: should be 0-2
            maxTokens: -100, // Invalid: should be positive
            topP: 1.5, // Invalid: should be 0-1
        );
        echo "✅ Parameters validated\n\n";
    } catch (InvalidArgumentException $e) {
        echo "❌ Validation failed: {$e->getMessage()}\n\n";
    }

    // Test 3: Validate model name
    echo "Test 3: Model validation\n";
    try {
        $validModel = 'mistral-small-latest';
        validateModel($validModel);
        echo "✅ Model '{$validModel}' is valid\n\n";
    } catch (InvalidArgumentException $e) {
        echo "❌ Validation failed: {$e->getMessage()}\n\n";
    }

    echo "💡 Validation Benefits:\n";
    echo "  • Fail fast with clear error messages\n";
    echo "  • Save API calls for invalid requests\n";
    echo "  • Reduce costs from bad requests\n";
    echo "  • Improve debugging experience\n";
    echo "  • Better user error messages\n\n";

    echo "📋 Validation Checklist:\n";
    echo "  ✅ Message array not empty\n";
    echo "  ✅ Each message has role and content\n";
    echo "  ✅ Temperature in valid range (0-2)\n";
    echo "  ✅ maxTokens is positive\n";
    echo "  ✅ topP between 0 and 1\n";
    echo "  ✅ Model name is valid\n";
    echo "  ✅ API key is set and not empty\n\n";
}

/**
 * Example 5: Circuit breaker pattern
 */
function circuitBreakerPattern(Mistral $mistral): void
{
    displaySection('Example 5: Circuit Breaker Pattern');
    echo "Implementing circuit breaker for resilience...\n\n";

    $circuitBreaker = new SimpleCircuitBreaker(
        failureThreshold: 3,
        timeoutSeconds: 30,
    );

    echo "Circuit Breaker Configuration:\n";
    echo "  • Failure threshold: 3\n";
    echo "  • Timeout: 30 seconds\n";
    echo "  • Initial state: CLOSED (normal operation)\n\n";

    // Simulate multiple requests
    $requests = [
        ['success' => true, 'description' => 'Normal request'],
        ['success' => true, 'description' => 'Normal request'],
        ['success' => false, 'description' => 'Failed request (1/3)'],
        ['success' => false, 'description' => 'Failed request (2/3)'],
        ['success' => false, 'description' => 'Failed request (3/3)'],
        ['success' => true, 'description' => 'Request while circuit OPEN'],
        ['success' => true, 'description' => 'Request after timeout'],
    ];

    foreach ($requests as $i => $request) {
        echo 'Request '.($i + 1).": {$request['description']}\n";

        if ($circuitBreaker->isOpen()) {
            echo "⛔ Circuit OPEN - Request blocked\n";
            echo "Failures: {$circuitBreaker->getFailureCount()}\n";
            echo "State: {$circuitBreaker->getState()}\n\n";

            continue;
        }

        try {
            if (! $request['success']) {
                throw new RuntimeException('Simulated API failure');
            }

            $circuitBreaker->recordSuccess();
            echo "✅ Request succeeded\n";
            echo "State: {$circuitBreaker->getState()}\n\n";

        } catch (Throwable $e) {
            $circuitBreaker->recordFailure();
            echo "❌ Request failed\n";
            echo "Failures: {$circuitBreaker->getFailureCount()}\n";
            echo "State: {$circuitBreaker->getState()}\n\n";
        }
    }

    echo "💡 Circuit Breaker Benefits:\n";
    echo "  • Prevent cascading failures\n";
    echo "  • Give failing services time to recover\n";
    echo "  • Fail fast instead of waiting\n";
    echo "  • Reduce resource waste\n";
    echo "  • Automatic recovery attempts\n\n";

    echo "📊 Circuit States:\n";
    echo "  • CLOSED: Normal operation, requests pass through\n";
    echo "  • OPEN: Failure threshold exceeded, block requests\n";
    echo "  • HALF_OPEN: Testing if service recovered\n\n";
}

// Helper functions

function validateMessages(array $messages): void
{
    if (empty($messages)) {
        throw new InvalidArgumentException('Messages array cannot be empty');
    }

    foreach ($messages as $i => $message) {
        if (! is_array($message)) {
            throw new InvalidArgumentException("Message {$i} must be an array");
        }

        if (! isset($message['role'])) {
            throw new InvalidArgumentException("Message {$i} missing 'role' field");
        }

        if (! isset($message['content'])) {
            throw new InvalidArgumentException("Message {$i} missing 'content' field");
        }
    }
}

function validateChatParameters(?float $temperature = null, ?int $maxTokens = null, ?float $topP = null): void
{
    if ($temperature !== null && ($temperature < 0 || $temperature > 2)) {
        throw new InvalidArgumentException('Temperature must be between 0 and 2');
    }

    if ($maxTokens !== null && $maxTokens <= 0) {
        throw new InvalidArgumentException('maxTokens must be positive');
    }

    if ($topP !== null && ($topP < 0 || $topP > 1)) {
        throw new InvalidArgumentException('topP must be between 0 and 1');
    }
}

function validateModel(string $model): void
{
    $validModels = [
        'mistral-small-latest',
        'mistral-medium-latest',
        'mistral-large-latest',
        'open-mistral-7b',
        'open-mixtral-8x7b',
    ];

    if (! in_array($model, $validModels) && ! str_starts_with($model, 'ft:')) {
        throw new InvalidArgumentException("Invalid model: {$model}");
    }
}

/**
 * Simple circuit breaker implementation
 */
class SimpleCircuitBreaker
{
    private const STATE_CLOSED = 'CLOSED';

    private const STATE_OPEN = 'OPEN';

    private const STATE_HALF_OPEN = 'HALF_OPEN';

    private string $state = self::STATE_CLOSED;

    private int $failureCount = 0;

    private ?int $lastFailureTime = null;

    public function __construct(
        private readonly int $failureThreshold = 3,
        private readonly int $timeoutSeconds = 60,
    ) {}

    public function isOpen(): bool
    {
        if ($this->state === self::STATE_OPEN) {
            // Check if timeout has elapsed
            if ($this->lastFailureTime && (time() - $this->lastFailureTime) > $this->timeoutSeconds) {
                $this->state = self::STATE_HALF_OPEN;

                return false;
            }

            return true;
        }

        return false;
    }

    public function recordSuccess(): void
    {
        $this->failureCount = 0;
        $this->state = self::STATE_CLOSED;
    }

    public function recordFailure(): void
    {
        $this->failureCount++;
        $this->lastFailureTime = time();

        if ($this->failureCount >= $this->failureThreshold) {
            $this->state = self::STATE_OPEN;
        }
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function getState(): string
    {
        return $this->state;
    }
}

// Run the example if executed directly
if (php_sapi_name() === 'cli') {
    main();
}
