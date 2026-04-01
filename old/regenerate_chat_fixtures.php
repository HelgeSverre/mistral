<?php

require __DIR__.'/vendor/autoload.php';

use HelgeSverre\Mistral\Mistral;

if (file_exists(__DIR__.'/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$apiKey = $_ENV['MISTRAL_API_KEY'] ?? getenv('MISTRAL_API_KEY');
$mistral = new Mistral(apiKey: $apiKey);

function saveFixture(string $name, $response): void
{
    $fixtureDir = __DIR__.'/tests/Fixtures/Saloon';
    $fixture = [
        'statusCode' => $response->status(),
        'headers' => $response->headers()->all(),
        'data' => $response->body(),
        'context' => [],
    ];

    $filePath = "{$fixtureDir}/{$name}.json";
    file_put_contents($filePath, json_encode($fixture, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "✅ Saved: {$filePath}\n";
}

echo "Generating Chat fixtures...\n\n";

// 1. Basic chat completion
echo "1. Basic chat completion...\n";
$response = $mistral->chat()->create(
    messages: [
        ['role' => 'user', 'content' => 'Say the word "banana" in your response.'],
    ],
    model: 'open-mistral-7b'
);
if ($response->successful()) {
    saveFixture('chat.createChatCompletion', $response);
}

// 2. JSON mode chat completion
echo "\n2. JSON mode chat completion...\n";
$response = $mistral->chat()->create(
    messages: [
        ['role' => 'user', 'content' => 'Extract the information from this text: "John Doe is 30 years old. His email is john.doe@example.com" Return as JSON with keys: name, age, email'],
    ],
    model: 'mistral-small-latest',
    responseFormat: ['type' => 'json_object']
);
if ($response->successful()) {
    saveFixture('chat.createChatCompletion-jsonMode', $response);
}

// 3. Function calling
echo "\n3. Function calling chat completion...\n";
$response = $mistral->chat()->create(
    messages: [
        ['role' => 'user', 'content' => 'What is the weather in Paris?'],
    ],
    model: 'mistral-small-latest',
    tools: [
        [
            'type' => 'function',
            'function' => [
                'name' => 'searchWeather',
                'description' => 'Get the weather for a location',
                'parameters' => [
                    'type' => 'object',
                    'required' => ['location'],
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'The location to get the weather for.',
                        ],
                    ],
                ],
            ],
        ],
    ],
    toolChoice: 'any'
);
if ($response->successful()) {
    saveFixture('chat.createChatCompletion-functionCall', $response);
}

echo "\n✅ All Chat fixtures generated!\n";
