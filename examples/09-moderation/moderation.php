<?php

/**
 * Content Moderation
 *
 * Description: Use Mistral's moderation API to detect harmful or inappropriate content
 * Use Case: Content filtering, safety checks, community moderation, compliance
 * Prerequisites: MISTRAL_API_KEY in .env file
 *
 * Note: Moderation endpoints are part of the Classifications resource (tagged as 'classifiers' in the API).
 * You can use $mistral->classifications()->moderate() or the deprecated $mistral->moderations()->moderate().
 *
 * @see https://docs.mistral.ai/capabilities/guardrailing/
 */

declare(strict_types=1);

require_once __DIR__.'/../shared/bootstrap.php';

use HelgeSverre\Mistral\Enums\Role;
use HelgeSverre\Mistral\Mistral;

/**
 * Main execution function
 */
function main(): void
{
    displayTitle('Content Moderation', '🛡️');

    $mistral = createMistralClient();

    try {
        // Example 1: Basic text moderation
        basicModeration($mistral);

        // Example 2: Chat message moderation
        chatModeration($mistral);

        // Example 3: Multi-category moderation
        multiCategoryModeration($mistral);

        // Example 4: Moderation in a pipeline
        moderationPipeline($mistral);

    } catch (Throwable $e) {
        handleError($e);
    }
}

/**
 * Example 1: Basic text moderation
 */
function basicModeration(Mistral $mistral): void
{
    displaySection('Example 1: Basic Text Moderation');
    echo "Checking text content for policy violations...\n\n";

    // Test different types of content
    $testInputs = [
        'This is a perfectly safe and friendly message.',
        'Hello! How are you today?',
        'I love programming with PHP!',
    ];

    foreach ($testInputs as $i => $input) {
        echo 'Test '.($i + 1).": \"{$input}\"\n";
        echo str_repeat('─', 60)."\n";

        // Moderate the text
        $response = $mistral->classifications()->moderate(
            model: 'mistral-moderation-latest',
            input: $input,
        );

        $dto = $response->dto();

        // Check moderation results
        $result = $dto->results->first();

        if ($result) {
            // Overall assessment - using isFlagged() method
            $isSafe = ! $result->isFlagged();

            if ($isSafe) {
                echo "✅ Status: SAFE\n";
            } else {
                echo "⚠️ Status: FLAGGED\n";
            }

            // Display categories that were triggered
            echo "📊 Category Flags:\n";
            displayModerationCategories($result->categories);
        }

        echo "\n";
    }

    echo "💡 Content Moderation Use Cases:\n";
    echo "  • User-generated content filtering\n";
    echo "  • Comment section safety\n";
    echo "  • Chat message screening\n";
    echo "  • Form submission validation\n";
    echo "  • API input sanitization\n\n";
}

/**
 * Example 2: Moderating chat conversations
 */
function chatModeration(Mistral $mistral): void
{
    displaySection('Example 2: Chat Message Moderation');
    echo "Moderating multi-turn conversations...\n\n";

    // Simulate a conversation
    $conversation = [
        [
            'role' => Role::user->value,
            'content' => 'Hello! I need help with my programming project.',
        ],
        [
            'role' => Role::assistant->value,
            'content' => 'Hello! I would be happy to help with your programming project. What language are you using?',
        ],
        [
            'role' => Role::user->value,
            'content' => 'I am using PHP for a web application.',
        ],
    ];

    echo "Conversation:\n";
    foreach ($conversation as $i => $message) {
        echo '  '.($i + 1).". [{$message['role']}] {$message['content']}\n";
    }
    echo "\n";

    echo "🔄 Moderating conversation...\n";

    // Moderate the entire conversation
    $response = $mistral->classifications()->moderateChat(
        model: 'mistral-moderation-latest',
        messages: $conversation,
    );

    $dto = $response->dto();

    echo "✅ Moderation completed\n\n";

    // Check results for each message
    echo "📊 Message-by-Message Analysis:\n";
    echo str_repeat('─', 60)."\n\n";

    foreach ($dto->results as $i => $result) {
        $messageNum = $i + 1;
        $message = $conversation[$i];

        echo "Message {$messageNum} [{$message['role']}]:\n";
        echo "\"{$message['content']}\"\n";

        // Check if any categories were flagged using the isFlagged() method
        if ($result->isFlagged()) {
            echo "Status: ⚠️ FLAGGED\n";
            displayModerationCategories($result->categories);
        } else {
            echo "Status: ✅ SAFE\n";
        }

        echo "\n";
    }

    echo "💡 Chat Moderation Best Practices:\n";
    echo "  • Moderate messages before sending to model\n";
    echo "  • Check both user and assistant messages\n";
    echo "  • Log flagged content for review\n";
    echo "  • Provide user feedback on policy violations\n";
    echo "  • Implement escalation for repeated violations\n\n";
}

/**
 * Example 3: Detailed multi-category analysis
 */
function multiCategoryModeration(Mistral $mistral): void
{
    displaySection('Example 3: Multi-Category Analysis');
    echo "Understanding different moderation categories...\n\n";

    $testContent = 'This is a test message to check all moderation categories.';

    echo "Test content: \"{$testContent}\"\n\n";

    $response = $mistral->classifications()->moderate(
        model: 'mistral-moderation-latest',
        input: $testContent,
    );

    $dto = $response->dto();
    $result = $dto->results->first();

    echo "📋 Moderation Categories:\n";
    echo str_repeat('─', 60)."\n\n";

    $categories = [
        'sexual' => [
            'name' => 'Sexual Content',
            'description' => 'Sexually explicit or suggestive content',
        ],
        'hateAndDiscrimination' => [
            'name' => 'Hate & Discrimination',
            'description' => 'Content promoting hate or discrimination',
        ],
        'violenceAndThreats' => [
            'name' => 'Violence & Threats',
            'description' => 'Violent or threatening content',
        ],
        'dangerousAndCriminalContent' => [
            'name' => 'Dangerous & Criminal Content',
            'description' => 'Content related to dangerous or criminal activities',
        ],
        'selfharm' => [
            'name' => 'Self-Harm',
            'description' => 'Content related to self-harm or suicide',
        ],
        'health' => [
            'name' => 'Health',
            'description' => 'Medical or health-related content',
        ],
        'financial' => [
            'name' => 'Financial',
            'description' => 'Financial advice or scams',
        ],
        'law' => [
            'name' => 'Law',
            'description' => 'Legal advice or law-related content',
        ],
        'pii' => [
            'name' => 'PII (Personal Info)',
            'description' => 'Personally identifiable information',
        ],
    ];

    foreach ($categories as $key => $info) {
        $value = $result->categories->$key;
        $status = $value ? '⚠️ FLAGGED' : '✅ Safe';

        echo "{$info['name']}:\n";
        echo "  Status: {$status}\n";
        echo "  Description: {$info['description']}\n\n";
    }

    echo "💡 Category Guidelines:\n";
    echo "  • Different categories have different severity levels\n";
    echo "  • Combine with your own custom rules\n";
    echo "  • Consider context when taking action\n";
    echo "  • Document moderation decisions\n";
    echo "  • Review flagged content regularly\n\n";
}

/**
 * Example 4: Integrating moderation into a content pipeline
 */
function moderationPipeline(Mistral $mistral): void
{
    displaySection('Example 4: Moderation Pipeline');
    echo "Building a complete content moderation workflow...\n\n";

    // Simulated user input
    $userInput = 'Can you help me write a function to validate email addresses in PHP?';

    echo "Step 1: Receive User Input\n";
    echo str_repeat('─', 40)."\n";
    echo "User: {$userInput}\n\n";

    // Step 2: Pre-moderation check
    echo "Step 2: Pre-Moderation Check\n";
    echo str_repeat('─', 40)."\n";

    $moderationResponse = $mistral->classifications()->moderate(
        model: 'mistral-moderation-latest',
        input: $userInput,
    );

    $moderationResult = $moderationResponse->dto()->results->first();

    // Check if content is safe using the isFlagged() method
    $isSafe = ! $moderationResult->isFlagged();
    $flaggedCategories = [];

    if ($moderationResult->isFlagged()) {
        // Collect flagged categories for reporting
        $categoriesObj = $moderationResult->categories;
        if ($categoriesObj->sexual) {
            $flaggedCategories[] = 'sexual';
        }
        if ($categoriesObj->hateAndDiscrimination) {
            $flaggedCategories[] = 'hate_and_discrimination';
        }
        if ($categoriesObj->violenceAndThreats) {
            $flaggedCategories[] = 'violence_and_threats';
        }
        if ($categoriesObj->dangerousAndCriminalContent) {
            $flaggedCategories[] = 'dangerous_and_criminal_content';
        }
        if ($categoriesObj->selfharm) {
            $flaggedCategories[] = 'selfharm';
        }
        if ($categoriesObj->health) {
            $flaggedCategories[] = 'health';
        }
        if ($categoriesObj->financial) {
            $flaggedCategories[] = 'financial';
        }
        if ($categoriesObj->law) {
            $flaggedCategories[] = 'law';
        }
        if ($categoriesObj->pii) {
            $flaggedCategories[] = 'pii';
        }
    }

    if ($isSafe) {
        echo "✅ Content passed moderation\n\n";

        // Step 3: Process the content (send to AI)
        echo "Step 3: Processing Request\n";
        echo str_repeat('─', 40)."\n";

        $messages = [
            ['role' => Role::user->value, 'content' => $userInput],
        ];

        $chatResponse = $mistral->chat()->create(
            messages: $messages,
            model: 'mistral-small-latest',
            maxTokens: 500,
        );

        $aiResponse = $chatResponse->dto()->choices[0]->message->content;

        echo "AI Response generated\n\n";

        // Step 4: Post-moderation check
        echo "Step 4: Post-Moderation Check\n";
        echo str_repeat('─', 40)."\n";

        $outputModeration = $mistral->classifications()->moderate(
            model: 'mistral-moderation-latest',
            input: $aiResponse,
        );

        $outputResult = $outputModeration->dto()->results->first();

        // Check using the isFlagged() method
        if (! $outputResult->isFlagged()) {
            echo "✅ AI response passed moderation\n\n";

            // Step 5: Return to user
            echo "Step 5: Deliver Response\n";
            echo str_repeat('─', 40)."\n";
            echo 'Response: '.substr($aiResponse, 0, 200)."...\n\n";
        } else {
            echo "⚠️ AI response flagged by moderation\n";
            echo "Action: Response blocked, alternative generated\n\n";
        }

    } else {
        echo "⚠️ Content flagged by moderation\n";
        echo 'Flagged categories: '.implode(', ', $flaggedCategories)."\n";
        echo "Action: Request rejected\n\n";

        // Provide feedback to user
        echo 'User feedback: "Your message was flagged for policy violations. ';
        echo "Please review our content guidelines.\"\n\n";
    }

    echo "💡 Pipeline Best Practices:\n";
    echo "  ✅ Moderate input before processing\n";
    echo "  ✅ Moderate output before delivery\n";
    echo "  ✅ Log all moderation decisions\n";
    echo "  ✅ Provide clear user feedback\n";
    echo "  ✅ Implement graceful degradation\n";
    echo "  ✅ Monitor false positives\n";
    echo "  ✅ Review and update policies regularly\n\n";

    echo "🔒 Security Considerations:\n";
    echo "  • Never trust user input\n";
    echo "  • Validate and sanitize all data\n";
    echo "  • Rate limit moderation checks\n";
    echo "  • Cache results for duplicate content\n";
    echo "  • Implement appeals process\n";
    echo "  • Comply with regional regulations\n";
    echo "  • Document moderation decisions\n";
    echo "  • Train team on moderation policies\n";
}

/**
 * Helper function to display moderation categories
 */
function displayModerationCategories(object $categories): void
{
    $flagged = [];

    if ($categories->sexual) {
        $flagged[] = 'Sexual';
    }
    if ($categories->hateAndDiscrimination) {
        $flagged[] = 'Hate & Discrimination';
    }
    if ($categories->violenceAndThreats) {
        $flagged[] = 'Violence & Threats';
    }
    if ($categories->dangerousAndCriminalContent) {
        $flagged[] = 'Dangerous & Criminal Content';
    }
    if ($categories->selfharm) {
        $flagged[] = 'Self-Harm';
    }
    if ($categories->health) {
        $flagged[] = 'Health';
    }
    if ($categories->financial) {
        $flagged[] = 'Financial';
    }
    if ($categories->law) {
        $flagged[] = 'Law';
    }
    if ($categories->pii) {
        $flagged[] = 'PII';
    }

    if (! empty($flagged)) {
        echo '  Flagged: '.implode(', ', $flagged)."\n";
    } else {
        echo "  No flags\n";
    }
}

// Run the example if executed directly
if (php_sapi_name() === 'cli') {
    main();
}
