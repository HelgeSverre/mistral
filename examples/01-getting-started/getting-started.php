<?php

/**
 * Getting Started with Mistral PHP SDK
 *
 * Description: This example demonstrates the basic setup and your first API call
 * Use Case: Verify SDK installation and API key configuration
 * Prerequisites: MISTRAL_API_KEY in .env file
 *
 * @see https://docs.mistral.ai/getting-started/quickstart/
 */

declare(strict_types=1);

require_once __DIR__.'/../shared/bootstrap.php';

use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Enums\Role;
use HelgeSverre\Mistral\Mistral;

/**
 * Main execution function
 */
function main(): void
{
    displayTitle('Getting Started with Mistral PHP SDK', '🚀');

    try {
        // Step 1: Initialize the Mistral client
        // The API key is loaded from the .env file automatically
        // SECURITY NOTE: Never hardcode your API key in your source code
        displaySection('Step 1: Initialize Client');
        echo "Creating Mistral client with API key from environment...\n";

        $mistral = createMistralClient();
        echo "✅ Client initialized successfully\n";

        // Step 2: Make your first API call
        // We'll use a simple chat completion to verify everything works
        displaySection('Step 2: Your First API Call');
        echo "Sending a simple message to Mistral AI...\n\n";

        // Create a basic message array
        // Messages have two required fields: 'role' and 'content'
        // The 'role' can be: system, user, or assistant
        $messages = [
            [
                'role' => Role::user->value,
                'content' => 'Say "Hello from Mistral PHP SDK!" and explain what you are in one sentence.',
            ],
        ];

        // Make the API call with minimal parameters
        // model: The AI model to use (defaults to mistral-small-latest)
        // messages: Array of conversation messages
        // maxTokens: Maximum number of tokens to generate (default: 1000)
        $response = $mistral->chat()->create(
            messages: $messages,
            model: Model::small->value,
            maxTokens: 100,
        );

        // Step 3: Process and display the response
        displaySection('Step 3: Process Response');

        // Check if the API call was successful
        if ($response->successful()) {
            echo "✅ API call successful!\n\n";

            // The response is a Saloon Response object
            // We can convert it to a DTO (Data Transfer Object) for type-safe access
            $dto = $response->dtoOrFail();

            // Display the AI's response
            printResponse($dto, showMetadata: true);

            // Access specific response fields
            $firstChoice = $dto->choices->first();
            if (! $firstChoice) {
                echo "❌ No choices returned in response\n";

                return;
            }
            $messageContent = $firstChoice->message->content;

            echo "📋 Response Details:\n";
            echo "  • Model used: {$dto->model}\n";
            echo "  • Finish reason: {$firstChoice->finishReason}\n";
            echo "  • Message role: {$firstChoice->message->role}\n";
            echo '  • Content length: '.strlen($messageContent)." characters\n";
        } else {
            // Handle unsuccessful responses
            echo "❌ API call failed with status: {$response->status()}\n";
            echo "Response: {$response->body()}\n";
        }

        // Step 4: Verify SDK installation
        displaySection('Step 4: Verify Installation');
        verifyInstallation($mistral);

    } catch (Throwable $e) {
        handleError($e);
    }
}

/**
 * Verify that the SDK is properly installed and configured
 */
function verifyInstallation(Mistral $mistral): void
{
    echo "Checking SDK installation...\n\n";

    // Check PHP version
    $phpVersion = PHP_VERSION;
    $requiredVersion = '8.2.0';
    $phpOk = version_compare($phpVersion, $requiredVersion, '>=');

    echo "✓ PHP Version:\n";
    echo "  • Current: {$phpVersion}\n";
    echo "  • Required: {$requiredVersion}+\n";
    echo '  • Status: '.($phpOk ? '✅ OK' : '❌ Upgrade needed')."\n\n";

    // Check API key configuration
    $apiKeyConfigured = ! empty($_ENV['MISTRAL_API_KEY']) &&
        $_ENV['MISTRAL_API_KEY'] !== 'your_api_key_here';

    echo "✓ API Key Configuration:\n";
    echo '  • Status: '.($apiKeyConfigured ? '✅ Configured' : '❌ Not configured')."\n";
    if ($apiKeyConfigured) {
        echo '  • Key prefix: '.substr($_ENV['MISTRAL_API_KEY'], 0, 8)."...\n";
    }
    echo "\n";

    // Check available models
    echo "✓ Listing Available Models:\n";
    try {
        $modelsResponse = $mistral->models()->list();
        if ($modelsResponse->successful()) {

            $models = $modelsResponse->dtoOrFail();
            echo '  • Total models available: '.count($models->data)."\n";

            // Display first 5 models
            echo "  • Sample models:\n";

            /** @var HelgeSverre\Mistral\Dto\Models\Model[] $sampleModels */
            $sampleModels = $models->data->toCollection()->take(5)->all();

            foreach ($sampleModels as $model) {
                echo "    - {$model->id}\n";
            }
        }
    } catch (Throwable $e) {
        echo "  • Status: ⚠️ Could not fetch models\n";
        echo "  • Error: {$e->getMessage()}\n";
    }

    echo "\n🎉 Installation verification complete!\n";
    echo "\n📚 Next Steps:\n";
    echo "  1. Explore basic chat completions: examples/02-basic-chat/\n";
    echo "  2. Learn about chat parameters: examples/03-chat-parameters/\n";
    echo "  3. Try streaming responses: examples/04-streaming-chat/\n";
    echo "  4. Implement function calling: examples/05-function-calling/\n";
}

// Run the example if executed directly
if (php_sapi_name() === 'cli') {
    main();
}
