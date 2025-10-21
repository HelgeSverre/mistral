<?php

/**
 * Basic Chat Completions
 *
 * Description: Learn how to create chat completions with the Mistral API
 * Use Case: Building conversational AI applications, Q&A systems, and chatbots
 * Prerequisites: MISTRAL_API_KEY in .env file
 *
 * @see https://docs.mistral.ai/capabilities/completion/
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
    displayTitle('Basic Chat Completions', '💬');

    $mistral = createMistralClient();

    try {
        // Example 1: Simple single-turn conversation
        simpleQA($mistral);

        // Example 2: Multi-turn conversation with context
        multiTurnConversation($mistral);

        // Example 3: Using system prompts to control behavior
        systemPromptExample($mistral);

        // Example 4: Different message roles
        messageRolesExample($mistral);

    } catch (Throwable $e) {
        handleError($e);
    }
}

/**
 * Example 1: Simple question-answer interaction
 */
function simpleQA(Mistral $mistral): void
{
    displaySection('Example 1: Simple Q&A');
    echo "Asking a straightforward question to Mistral AI...\n\n";

    // Create a simple user message
    // This is the most basic form of chat completion
    $messages = [
        [
            'role' => Role::user->value,
            'content' => 'What is the capital of France? Please answer in one sentence.',
        ],
    ];

    // Make the API call
    $response = $mistral->chat()->create(
        messages: $messages,
        model: Model::small->value,
        maxTokens: 50,
        temperature: 0.7, // Default temperature for balanced creativity/consistency
    );

    // Display the response
    $dto = $response->dtoOrFail();
    printResponse($dto);

    // Extract just the text content
    $firstChoice = $dto->choices->first();
    if (! $firstChoice) {
        echo "❌ No response received from API\n";

        return;
    }
    $answer = $firstChoice->message->content;
    echo "📝 Answer: {$answer}\n";
}

/**
 * Example 2: Multi-turn conversation maintaining context
 */
function multiTurnConversation(Mistral $mistral): void
{
    displaySection('Example 2: Multi-turn Conversation');
    echo "Having a multi-turn conversation with context...\n\n";

    // Conversation history is maintained by including previous messages
    // The API is stateless, so you must send the full conversation each time
    $conversation = [
        [
            'role' => Role::user->value,
            'content' => 'I am planning a trip to Japan. What is the best season to visit?',
        ],
    ];

    // Turn 1: Initial question
    echo "🧑 User: {$conversation[0]['content']}\n\n";

    $response1 = $mistral->chat()->create(
        messages: $conversation,
        model: Model::small->value,
        maxTokens: 200,
    );

    $dto1 = $response1->dtoOrFail();
    $choice1 = $dto1->choices->first();
    if (! $choice1) {
        echo "❌ No response received from API\n";

        return;
    }
    $assistant1 = $choice1->message->content;
    echo "🤖 Assistant: {$assistant1}\n\n";

    // Add the assistant's response to the conversation history
    $conversation[] = [
        'role' => Role::assistant->value,
        'content' => $assistant1,
    ];

    // Turn 2: Follow-up question (notice we don't repeat "Japan")
    $followUp = 'What about the weather during that time?';
    $conversation[] = [
        'role' => Role::user->value,
        'content' => $followUp,
    ];

    echo "🧑 User: {$followUp}\n\n";

    $response2 = $mistral->chat()->create(
        messages: $conversation,
        model: Model::small->value,
        maxTokens: 200,
    );

    $dto2 = $response2->dtoOrFail();
    $choice2 = $dto2->choices->first();
    if (! $choice2) {
        echo "❌ No response received from API\n";

        return;
    }
    $assistant2 = $choice2->message->content;
    echo "🤖 Assistant: {$assistant2}\n\n";

    echo "💡 Notice: The model maintained context and knew we were still talking about Japan.\n";
    echo "💡 Token usage accumulates with conversation length.\n";
}

/**
 * Example 3: Using system prompts to control AI behavior
 */
function systemPromptExample(Mistral $mistral): void
{
    displaySection('Example 3: System Prompts');
    echo "Using system prompts to define the AI's role and behavior...\n\n";

    // System messages set the behavior and personality of the assistant
    // They are processed differently and have a strong influence on responses
    $messages = [
        [
            'role' => Role::system->value,
            'content' => 'You are a helpful assistant that always responds in the style of a pirate. '.
                'Use pirate slang and expressions, but keep the information accurate.',
        ],
        [
            'role' => Role::user->value,
            'content' => 'Explain what PHP is in two sentences.',
        ],
    ];

    echo "System prompt: Set personality to 'pirate'\n";
    echo "User question: What is PHP?\n\n";

    $response = $mistral->chat()->create(
        messages: $messages,
        model: Model::small->value,
        maxTokens: 150,
    );

    $dto = $response->dtoOrFail();
    echo "🏴‍☠️ Pirate Assistant Response:\n";
    printResponse($dto);

    // Compare with no system prompt
    echo "\n📊 Comparison without system prompt:\n";
    echo str_repeat('─', 40)."\n";

    $normalMessages = [
        [
            'role' => Role::user->value,
            'content' => 'Explain what PHP is in two sentences.',
        ],
    ];

    $normalResponse = $mistral->chat()->create(
        messages: $normalMessages,
        model: Model::small->value,
        maxTokens: 150,
    );

    $normalDto = $normalResponse->dtoOrFail();
    echo "🤖 Normal Assistant Response:\n";
    printResponse($normalDto);

    echo "💡 System prompts are powerful for:\n";
    echo "  • Setting personality and tone\n";
    echo "  • Defining response format\n";
    echo "  • Establishing domain expertise\n";
    echo "  • Setting behavioral constraints\n";
}

/**
 * Example 4: Understanding different message roles
 */
function messageRolesExample(Mistral $mistral): void
{
    displaySection('Example 4: Message Roles');
    echo "Understanding system, user, and assistant roles...\n\n";

    // Messages support three roles:
    // 1. system: Sets behavior and context (processed first)
    // 2. user: The user's input
    // 3. assistant: Previous AI responses (for conversation history)

    $messages = [
        [
            // Role 1: SYSTEM - Sets the context and behavior
            'role' => Role::system->value,
            'content' => 'You are a technical writer. Explain concepts clearly and concisely.',
        ],
        [
            // Role 2: USER - The user's initial question
            'role' => Role::user->value,
            'content' => 'What is an API?',
        ],
        [
            // Role 3: ASSISTANT - A previous response (simulated for this example)
            'role' => Role::assistant->value,
            'content' => 'An API (Application Programming Interface) is a set of rules and protocols '.
                'that allows different software applications to communicate with each other.',
        ],
        [
            // Role 2: USER - Follow-up question
            'role' => Role::user->value,
            'content' => 'Can you give a real-world example?',
        ],
    ];

    echo "Message structure:\n";
    echo "  1. SYSTEM: Defines behavior (technical writer)\n";
    echo "  2. USER: Initial question\n";
    echo "  3. ASSISTANT: Previous response\n";
    echo "  4. USER: Follow-up question\n\n";

    $response = $mistral->chat()->create(
        messages: $messages,
        model: Model::small->value,
        maxTokens: 200,
    );

    $dto = $response->dtoOrFail();
    printResponse($dto);

    echo "📚 Best Practices:\n";
    echo "  • Use system messages sparingly (they use tokens)\n";
    echo "  • Keep conversation history to relevant turns only\n";
    echo "  • Clear old messages to manage token usage\n";
    echo "  • System messages should be first in the array\n";
    echo "  • Alternate between user and assistant for history\n";
}

// Run the example if executed directly
if (php_sapi_name() === 'cli') {
    main();
}
