<?php

/**
 * Chat Completion Parameters
 *
 * Description: Master the art of controlling AI behavior with generation parameters
 * Use Case: Fine-tuning model responses for different use cases
 * Prerequisites: MISTRAL_API_KEY in .env file
 *
 * @see https://docs.mistral.ai/api/#operation/createChatCompletion
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
    displayTitle('Chat Completion Parameters', '⚙️');

    $mistral = createMistralClient();

    try {
        // Example 1: Temperature - Controls randomness
        temperatureExample($mistral);

        // Example 2: Max tokens - Controls response length
        maxTokensExample($mistral);

        // Example 3: Top P - Alternative to temperature
        topPExample($mistral);

        // Example 4: JSON mode - Structured outputs
        jsonModeExample($mistral);

        // Example 5: Stop sequences - Control where generation ends
        stopSequenceExample($mistral);

        // Example 6: Presence and frequency penalties
        penaltiesExample($mistral);

    } catch (Throwable $e) {
        handleError($e);
    }
}

/**
 * Example 1: Temperature controls randomness and creativity
 */
function temperatureExample(Mistral $mistral): void
{
    displaySection('Example 1: Temperature (Creativity Control)');
    echo "Comparing different temperature values...\n\n";

    // Temperature ranges from 0 to 1 (or slightly higher)
    // - 0.0: Deterministic, consistent responses
    // - 0.7: Balanced (default)
    // - 1.0+: Creative, diverse responses

    $prompt = 'Write a creative tagline for a coffee shop in 5 words.';
    $temperatures = [0.0, 0.5, 1.0];

    foreach ($temperatures as $temp) {
        echo "🌡️ Temperature: {$temp}\n";
        echo str_repeat('─', 40)."\n";

        $messages = [
            ['role' => Role::user->value, 'content' => $prompt],
        ];

        $response = $mistral->chat()->create(
            messages: $messages,
            model: Model::small->value,
            temperature: $temp,
            maxTokens: 30,
        );

        $dto = $response->dto();
        $choice = $dto->choices->first();
        if (! $choice) {
            echo "❌ No response received\n\n";

            continue;
        }
        $content = $choice->message->content;
        echo "Response: {$content}\n\n";
    }

    echo "💡 Temperature Best Practices:\n";
    echo "  • 0.0-0.3: Factual tasks, code generation, data extraction\n";
    echo "  • 0.4-0.7: Balanced use cases (default: 0.7)\n";
    echo "  • 0.8-1.0: Creative writing, brainstorming, marketing\n";
    echo "  • >1.0: Experimental, highly creative (use with caution)\n\n";
}

/**
 * Example 2: Max tokens controls response length
 */
function maxTokensExample(Mistral $mistral): void
{
    displaySection('Example 2: Max Tokens (Length Control)');
    echo "Controlling response length with maxTokens...\n\n";

    // Tokens are chunks of text (roughly 4 characters per token)
    // maxTokens sets the maximum length of the generated response
    // Note: This does NOT include the prompt tokens

    $prompt = 'Explain quantum computing.';
    $tokenLimits = [20, 50, 150];

    foreach ($tokenLimits as $limit) {
        echo "📏 Max Tokens: {$limit}\n";
        echo str_repeat('─', 40)."\n";

        $messages = [
            ['role' => Role::user->value, 'content' => $prompt],
        ];

        $response = $mistral->chat()->create(
            messages: $messages,
            model: Model::small->value,
            maxTokens: $limit,
            temperature: 0.7,
        );

        $dto = $response->dto();
        $choice = $dto->choices->first();
        if (! $choice) {
            echo "❌ No response received\n\n";

            continue;
        }
        $content = $choice->message->content;
        $actualTokens = $dto->usage->completionTokens;

        echo "Response: {$content}\n";
        echo "Tokens used: {$actualTokens}/{$limit}\n";
        echo "Finish reason: {$choice->finishReason}\n\n";
    }

    echo "💡 Token Best Practices:\n";
    echo "  • 1 token ≈ 4 characters (varies by language)\n";
    echo "  • Set limits to control costs\n";
    echo "  • Monitor usage via response->usage\n";
    echo "  • finish_reason='length' means limit was hit\n";
    echo "  • finish_reason='stop' means natural completion\n\n";
}

/**
 * Example 3: Top P (nucleus sampling) as alternative to temperature
 */
function topPExample(Mistral $mistral): void
{
    displaySection('Example 3: Top P (Nucleus Sampling)');
    echo "Using top_p for controlled randomness...\n\n";

    // top_p (0.0 to 1.0) considers only the top probability mass
    // - 1.0: Consider all tokens (default)
    // - 0.1: Consider only top 10% most likely tokens
    // Recommendation: Alter temperature OR top_p, not both

    $prompt = 'Complete this sentence: The future of AI will be';
    $topPValues = [0.1, 0.5, 1.0];

    foreach ($topPValues as $topP) {
        echo "🎯 Top P: {$topP}\n";
        echo str_repeat('─', 40)."\n";

        $messages = [
            ['role' => Role::user->value, 'content' => $prompt],
        ];

        $response = $mistral->chat()->create(
            messages: $messages,
            model: Model::small->value,
            temperature: 1.0, // Use higher temperature to see top_p effect
            topP: $topP,
            maxTokens: 50,
        );

        $dto = $response->dto();
        $choice = $dto->choices->first();
        if (! $choice) {
            echo "❌ No response received\n\n";

            continue;
        }
        $content = $choice->message->content;
        echo "Response: {$content}\n\n";
    }

    echo "💡 Top P vs Temperature:\n";
    echo "  • Temperature: Adjusts overall randomness\n";
    echo "  • Top P: Limits token selection pool\n";
    echo "  • Recommendation: Use temperature OR top_p, not both\n";
    echo "  • Default: top_p=1.0, temperature=0.7\n\n";
}

/**
 * Example 4: JSON mode for structured outputs
 */
function jsonModeExample(Mistral $mistral): void
{
    displaySection('Example 4: JSON Mode (Structured Output)');
    echo "Generating structured JSON responses...\n\n";

    // JSON mode ensures the output is valid JSON
    // You must explicitly ask for JSON in your prompt
    // Useful for API responses, data extraction, structured data

    $messages = [
        [
            'role' => Role::user->value,
            'content' => 'Generate a JSON object with information about a fictional person. '.
                'Include fields: name, age, occupation, and hobbies (array).',
        ],
    ];

    $response = $mistral->chat()->create(
        messages: $messages,
        model: Model::small->value,
        maxTokens: 200,
        responseFormat: ['type' => 'json_object'],
    );

    $dto = $response->dto();
    $choice = $dto->choices->first();
    if (! $choice) {
        echo "❌ No response received\n\n";

        return;
    }
    $jsonContent = $choice->message->content;

    echo "Raw JSON response:\n";
    echo $jsonContent."\n\n";

    // Parse and display the JSON
    $parsed = json_decode($jsonContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo '❌ JSON decode error: '.json_last_error_msg()."\n\n";

        return;
    }

    if ($parsed) {
        printJson($parsed, 'Parsed JSON Object');

        echo "✅ JSON is valid and can be used in your application\n";
        echo "📊 Name: {$parsed['name']}\n";
        echo "📊 Age: {$parsed['age']}\n";
        echo "📊 Occupation: {$parsed['occupation']}\n";
        echo '📊 Hobbies: '.implode(', ', $parsed['hobbies'])."\n\n";
    }

    echo "💡 JSON Mode Best Practices:\n";
    echo "  • Always request JSON in your prompt\n";
    echo "  • Specify the exact schema you need\n";
    echo "  • Validate the JSON before using it\n";
    echo "  • Perfect for API integrations\n";
    echo "  • Use for data extraction tasks\n\n";
}

/**
 * Example 5: Stop sequences control where generation ends
 */
function stopSequenceExample(Mistral $mistral): void
{
    displaySection('Example 5: Stop Sequences');
    echo "Using stop sequences to control output boundaries...\n\n";

    // Stop sequences tell the model when to stop generating
    // Useful for structured outputs, lists, or bounded responses

    echo "Example 1: Single stop sequence\n";
    echo str_repeat('─', 40)."\n";

    $messages = [
        [
            'role' => Role::user->value,
            'content' => 'List 3 programming languages:\n1.',
        ],
    ];

    $response = $mistral->chat()->create(
        messages: $messages,
        model: Model::small->value,
        maxTokens: 100,
        stop: "\n4.", // Stop when reaching item 4
    );

    $dto = $response->dto();
    $choice = $dto->choices->first();
    if (! $choice) {
        echo "❌ No response received\n\n";
    } else {
        $content = $choice->message->content;
        echo "Response:\n1.{$content}\n\n";
    }

    echo "Example 2: Multiple stop sequences\n";
    echo str_repeat('─', 40)."\n";

    $messages2 = [
        [
            'role' => Role::user->value,
            'content' => 'Write a short story. Start with "Once upon a time"',
        ],
    ];

    $response2 = $mistral->chat()->create(
        messages: $messages2,
        model: Model::small->value,
        maxTokens: 200,
        stop: ['The End', 'THE END', 'The end.'], // Multiple stop options
    );

    $dto2 = $response2->dto();
    $choice2 = $dto2->choices->first();
    if (! $choice2) {
        echo "❌ No response received\n\n";
    } else {
        $content2 = $choice2->message->content;
        echo "Response:\n{$content2}\n\n";
    }

    echo "💡 Stop Sequences Use Cases:\n";
    echo "  • Control list length\n";
    echo "  • End stories at specific points\n";
    echo "  • Extract structured data\n";
    echo "  • Prevent unwanted continuations\n\n";
}

/**
 * Example 6: Presence and frequency penalties
 */
function penaltiesExample(Mistral $mistral): void
{
    displaySection('Example 6: Presence & Frequency Penalties');
    echo "Controlling repetition with penalty parameters...\n\n";

    // Presence penalty: Penalizes tokens that have appeared (encourages topic diversity)
    // Frequency penalty: Penalizes tokens based on how often they appear (reduces repetition)
    // Range: -2.0 to 2.0 (positive values discourage, negative values encourage)

    $prompt = 'Write 3 sentences about technology.';

    echo "Without penalties (baseline):\n";
    echo str_repeat('─', 40)."\n";

    $messages = [['role' => Role::user->value, 'content' => $prompt]];

    $response1 = $mistral->chat()->create(
        messages: $messages,
        model: Model::small->value,
        maxTokens: 150,
    );

    $dto1 = $response1->dto();
    $choice1 = $dto1->choices->first();
    if ($choice1) {
        echo $choice1->message->content."\n\n";
    } else {
        echo "❌ No response received\n\n";
    }

    echo "With frequency penalty (reduces word repetition):\n";
    echo str_repeat('─', 40)."\n";

    $response2 = $mistral->chat()->create(
        messages: $messages,
        model: Model::small->value,
        maxTokens: 150,
        frequencyPenalty: 0.8,
    );

    $dto2 = $response2->dto();
    $choice2 = $dto2->choices->first();
    if ($choice2) {
        echo $choice2->message->content."\n\n";
    } else {
        echo "❌ No response received\n\n";
    }

    echo "With presence penalty (encourages topic diversity):\n";
    echo str_repeat('─', 40)."\n";

    $response3 = $mistral->chat()->create(
        messages: $messages,
        model: Model::small->value,
        maxTokens: 150,
        presencePenalty: 0.8,
    );

    $dto3 = $response3->dto();
    $choice3 = $dto3->choices->first();
    if ($choice3) {
        echo $choice3->message->content."\n\n";
    } else {
        echo "❌ No response received\n\n";
    }

    echo "💡 Penalties Best Practices:\n";
    echo "  • frequency_penalty: Reduces word repetition\n";
    echo "  • presence_penalty: Encourages topic variety\n";
    echo "  • Use positive values (0.0 to 1.0) for most cases\n";
    echo "  • Combine with temperature for fine control\n";
    echo "  • Test different values for your use case\n\n";
}

// Run the example if executed directly
if (php_sapi_name() === 'cli') {
    main();
}
