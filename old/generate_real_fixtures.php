<?php

require __DIR__.'/vendor/autoload.php';

use HelgeSverre\Mistral\Dto\Chat\ChatCompletionRequest;
use HelgeSverre\Mistral\Dto\Fim\FIMCompletionRequest;
use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Enums\Role;
use HelgeSverre\Mistral\Mistral;

// Bootstrap Laravel for testing
$app = require_once __DIR__.'/vendor/orchestra/testbench-core/laravel/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['MISTRAL_API_KEY'] ?? null;

if (! $apiKey) {
    echo "Error: MISTRAL_API_KEY not found in .env file\n";
    exit(1);
}

$mistral = new Mistral($apiKey);

echo "Starting real fixture generation...\n\n";

// Helper function to save fixture
function saveFixture(string $path, $response): void
{
    $fixtureData = [
        'statusCode' => $response->status(),
        'headers' => $response->headers(),
        'data' => $response->body(),
        'context' => [],
    ];

    $fullPath = __DIR__.'/tests/Fixtures/Saloon/'.$path;
    $dir = dirname($fullPath);

    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    file_put_contents($fullPath, json_encode($fixtureData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "✓ Saved: {$path}\n";
}

// 1. SIMPLE CHAT FIXTURES
echo "=== Generating SimpleChat Fixtures ===\n";

try {
    // Basic simple chat with open-mistral-7b
    $chatRequest = new \HelgeSverre\Mistral\Requests\Chat\CreateChatCompletion(
        new ChatCompletionRequest(
            model: 'open-mistral-7b',
            messages: [
                ['role' => Role::user->value, 'content' => 'Say the word "banana" and nothing else.'],
            ],
            maxTokens: 100
        )
    );
    $response = $mistral->send($chatRequest);
    saveFixture('simpleChat.createChatCompletion.json', $response);

    // Simple chat with JSON mode
    $chatRequest = new \HelgeSverre\Mistral\Requests\Chat\CreateChatCompletion(
        new ChatCompletionRequest(
            model: Model::small->value,
            messages: [
                ['role' => Role::user->value, 'content' => 'Generate a single JSON object with these exact fields: name: "John Doe", age: 30, email: "johndoe@example.com"'],
            ],
            maxTokens: 100,
            responseFormat: ['type' => 'json_object']
        )
    );
    $response = $mistral->send($chatRequest);
    saveFixture('simpleChat.createChatCompletion-jsonMode.json', $response);

} catch (Exception $e) {
    echo 'Error generating SimpleChat fixtures: '.$e->getMessage()."\n";
    echo $e->getTraceAsString()."\n";
}

// 2. CHAT FIXTURES
echo "\n=== Generating Chat Fixtures ===\n";

try {
    // Chat with JSON mode
    $chatRequest = new \HelgeSverre\Mistral\Requests\Chat\CreateChatCompletion(
        new ChatCompletionRequest(
            model: Model::small->value,
            messages: [
                ['role' => Role::user->value, 'content' => 'Generate a JSON object with these exact fields: name: "John Doe", age: 30, email: "johndoe@example.com"'],
            ],
            responseFormat: ['type' => 'json_object'],
            maxTokens: 150
        )
    );
    $response = $mistral->send($chatRequest);
    saveFixture('chat.createChatCompletion-jsonMode.json', $response);

    // Chat with function calling
    $chatRequest = new \HelgeSverre\Mistral\Requests\Chat\CreateChatCompletion(
        new ChatCompletionRequest(
            model: Model::small->value,
            messages: [
                ['role' => Role::user->value, 'content' => 'What is the weather in Paris?'],
            ],
            tools: [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'searchWeather',
                        'description' => 'Search for weather in a location',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'location' => [
                                    'type' => 'string',
                                    'description' => 'The city name',
                                ],
                            ],
                            'required' => ['location'],
                        ],
                    ],
                ],
            ],
            toolChoice: 'any'
        )
    );
    $response = $mistral->send($chatRequest);
    saveFixture('chat.createChatCompletion-functionCall.json', $response);

    // Chat with open-mistral-7b model
    $chatRequest = new \HelgeSverre\Mistral\Requests\Chat\CreateChatCompletion(
        new ChatCompletionRequest(
            model: 'open-mistral-7b',
            messages: [
                ['role' => Role::user->value, 'content' => 'Say hello'],
            ],
            maxTokens: 50
        )
    );
    $response = $mistral->send($chatRequest);
    saveFixture('chat.createChatCompletion-with-open-mistral-7b-model.json', $response);

} catch (Exception $e) {
    echo 'Error generating Chat fixtures: '.$e->getMessage()."\n";
    echo $e->getTraceAsString()."\n";
}

// 3. CLASSIFICATIONS FIXTURES
// NOTE: Skipping classifications - model 'mistral-classifier-latest' not available
echo "\n=== Skipping Classifications (model not available) ===\n";

// 4. FIM FIXTURES
echo "\n=== Generating FIM Fixtures ===\n";

try {
    // Basic FIM completion
    $fimRequest = new \HelgeSverre\Mistral\Requests\Fim\CreateFIMCompletionRequest(
        new FIMCompletionRequest(
            model: 'codestral-latest',
            prompt: 'def fibonacci(n):',
            suffix: ''
        )
    );
    $response = $mistral->send($fimRequest);
    saveFixture('fim/completion.json', $response);

    // FIM with suffix
    $fimRequest = new \HelgeSverre\Mistral\Requests\Fim\CreateFIMCompletionRequest(
        new FIMCompletionRequest(
            model: 'codestral-latest',
            prompt: 'def hello(',
            suffix: '):\n    return "Hello"'
        )
    );
    $response = $mistral->send($fimRequest);
    saveFixture('fim/completion-with-suffix.json', $response);

} catch (Exception $e) {
    echo 'Error generating FIM fixtures: '.$e->getMessage()."\n";
    echo $e->getTraceAsString()."\n";
}

// 5. MODERATIONS FIXTURES
echo "\n=== Generating Moderations Fixtures ===\n";

try {
    // Safe text
    $response = $mistral->classifications()->moderate(
        model: 'mistral-moderation-latest',
        input: 'Hello, how are you today?'
    );
    saveFixture('moderations/text_safe.json', $response);

    // Flagged text
    $response = $mistral->classifications()->moderate(
        model: 'mistral-moderation-latest',
        input: 'I want to hurt someone'
    );
    saveFixture('moderations/text_flagged.json', $response);

    // Multiple texts
    $response = $mistral->classifications()->moderate(
        model: 'mistral-moderation-latest',
        input: [
            'This is a nice day',
            'I hate everyone',
        ]
    );
    saveFixture('moderations/text_multiple.json', $response);

    // Chat moderation
    $response = $mistral->classifications()->moderateChat(
        model: 'mistral-moderation-latest',
        input: [
            ['role' => 'user', 'content' => 'Hello'],
            ['role' => 'assistant', 'content' => 'Hi there!'],
        ]
    );
    saveFixture('moderations/chat.json', $response);

} catch (Exception $e) {
    echo 'Error generating Moderations fixtures: '.$e->getMessage()."\n";
    echo $e->getTraceAsString()."\n";
}

echo "\n✅ Fixture generation complete!\n";
