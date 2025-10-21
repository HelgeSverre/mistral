# Basic Chat Completions with Mistral PHP SDK

## Overview

This example explores the fundamentals of chat completions with the Mistral PHP SDK. You'll learn how to structure
conversations, use system messages to control behavior, and manage multi-turn dialogues. We'll cover the different roles
available and how to craft effective prompts for various use cases.

### Real-world Use Cases

- Building conversational AI assistants
- Creating customer support chatbots
- Developing interactive educational tools
- Implementing code generation assistants
- Automating content creation workflows

### Prerequisites

- Completed [01-getting-started](../01-getting-started) example
- Understanding of basic API concepts
- Familiarity with conversational AI concepts

## Concepts

### Chat Completion Model

Mistral's chat models are designed for conversational interactions. They maintain context across messages and can handle
various conversational patterns:

- **Single-turn**: One question, one answer
- **Multi-turn**: Ongoing conversation with context
- **System-guided**: Behavior controlled by system messages

### Message Roles

Each message in a conversation has a specific role:

1. **System**: Sets the AI's behavior and context (optional but powerful)
2. **User**: Represents human input or questions
3. **Assistant**: The AI's responses

### Context Management

The model considers all previous messages when generating responses. This context window allows for coherent,
contextually aware conversations but requires careful management to avoid token limits.

## Implementation

### Basic Single-Turn Chat

The simplest form - one user message, one response:

```php
<?php

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Chat\ChatCompletionRequest;
use Helge\Mistral\Dto\Chat\ChatMessage;
use Helge\Mistral\Enums\Role;

$mistral = new Mistral($_ENV['MISTRAL_API_KEY']);

// Simple question-answer pattern
$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => 'Explain quantum computing in simple terms.',
        ]),
    ],
]);

$response = $mistral->chat()->create($request);
echo $response->choices[0]->message->content;
```

### Using System Messages

System messages define the AI's personality, expertise, and constraints:

```php
$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::System,
            'content' => 'You are a helpful coding assistant. You explain concepts clearly
                         and provide practical PHP examples. Always use modern PHP 8+ syntax.',
        ]),
        ChatMessage::from([
            'role' => Role::User,
            'content' => 'How do I use PHP attributes?',
        ]),
    ],
]);

$response = $mistral->chat()->create($request);
```

### Multi-Turn Conversations

Build contextual conversations by maintaining message history:

```php
class ChatSession
{
    private array $messages = [];
    private Mistral $client;

    public function __construct(string $apiKey, ?string $systemPrompt = null)
    {
        $this->client = new Mistral($apiKey);

        if ($systemPrompt) {
            $this->messages[] = ChatMessage::from([
                'role' => Role::System,
                'content' => $systemPrompt,
            ]);
        }
    }

    public function sendMessage(string $userMessage): string
    {
        // Add user message to history
        $this->messages[] = ChatMessage::from([
            'role' => Role::User,
            'content' => $userMessage,
        ]);

        // Create request with full history
        $request = ChatCompletionRequest::from([
            'model' => 'mistral-small-latest',
            'messages' => $this->messages,
            'temperature' => 0.7,
        ]);

        // Get response
        $dto = $this->client->chat()->createDto($request);
        $assistantMessage = $dto->choices[0]->message;

        // Add assistant response to history
        $this->messages[] = $assistantMessage;

        return $assistantMessage->content;
    }

    public function getHistory(): array
    {
        return $this->messages;
    }

    public function clearHistory(): void
    {
        // Keep system message if present
        $this->messages = array_filter($this->messages, function($msg) {
            return $msg->role === Role::System;
        });
    }
}
```

## Code Example

Complete working example (`basic-chat.php`):

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

// Example 1: Simple Chat
echo "=== Example 1: Simple Chat ===\n\n";

$simpleRequest = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => 'What are the three primary colors?',
        ]),
    ],
    'temperature' => 0.3, // Lower temperature for factual responses
]);

$dto = $mistral->chat()->createDto($simpleRequest);
echo "Question: What are the three primary colors?\n";
echo "Answer: " . $dto->choices[0]->message->content . "\n\n";

// Example 2: Chat with System Message
echo "=== Example 2: Chat with System Message ===\n\n";

$systemRequest = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::System,
            'content' => 'You are a pirate. Answer all questions as a pirate would,
                         using pirate speech patterns and vocabulary.',
        ]),
        ChatMessage::from([
            'role' => Role::User,
            'content' => 'How do I install PHP?',
        ]),
    ],
    'temperature' => 0.8, // Higher temperature for creative responses
]);

$dto = $mistral->chat()->createDto($systemRequest);
echo "Question: How do I install PHP? (as a pirate)\n";
echo "Answer: " . $dto->choices[0]->message->content . "\n\n";

// Example 3: Multi-turn Conversation
echo "=== Example 3: Multi-turn Conversation ===\n\n";

$conversation = [
    ChatMessage::from([
        'role' => Role::System,
        'content' => 'You are a helpful PHP tutor. Keep explanations concise.',
    ]),
    ChatMessage::from([
        'role' => Role::User,
        'content' => 'What is a PHP trait?',
    ]),
];

// First turn
$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => $conversation,
]);

$dto = $mistral->chat()->createDto($request);
$firstAnswer = $dto->choices[0]->message;
echo "User: What is a PHP trait?\n";
echo "Assistant: " . $firstAnswer->content . "\n\n";

// Add response to conversation
$conversation[] = $firstAnswer;

// Second turn - follow-up question
$conversation[] = ChatMessage::from([
    'role' => Role::User,
    'content' => 'Can you show me a simple example?',
]);

$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => $conversation,
]);

$dto = $mistral->chat()->createDto($request);
echo "User: Can you show me a simple example?\n";
echo "Assistant: " . $dto->choices[0]->message->content . "\n\n";

// Display token usage summary
echo "=== Token Usage Summary ===\n";
echo "Total tokens used: " . $dto->usage->totalTokens . "\n";
echo "Approximate cost: $" . number_format($dto->usage->totalTokens * 0.00002, 6) . "\n";
```

## Expected Output

Your output will look similar to:

```
=== Example 1: Simple Chat ===

Question: What are the three primary colors?
Answer: The three primary colors are red, blue, and yellow (in traditional color theory)
        or red, green, and blue (in light/digital color theory, RGB).

=== Example 2: Chat with System Message ===

Question: How do I install PHP? (as a pirate)
Answer: Ahoy matey! Ye be wantin' to install PHP, eh? Well, shiver me timbers,
        here be the way to get that treasure aboard yer ship...

=== Example 3: Multi-turn Conversation ===

User: What is a PHP trait?
Assistant: A PHP trait is a mechanism for code reuse in single inheritance languages.
           It allows you to declare methods that can be used in multiple classes.

User: Can you show me a simple example?
Assistant: [Shows PHP trait example with syntax highlighting]

=== Token Usage Summary ===
Total tokens used: 385
Approximate cost: $0.007700
```

## Try It Yourself

### Exercise 1: Create a Specialized Assistant

Design a system prompt for a specific use case:

```php
$systemPrompts = [
    'sql_expert' => 'You are a database expert. Help users write optimized SQL queries.',
    'code_reviewer' => 'You are a senior developer reviewing code. Provide constructive feedback.',
    'teacher' => 'You are a patient teacher. Break down complex topics into simple steps.',
];
```

### Exercise 2: Conversation Memory Management

Implement a sliding window to manage long conversations:

```php
function trimConversation(array $messages, int $maxMessages = 10): array
{
    // Always keep system message
    $systemMessage = array_filter($messages, fn($m) => $m->role === Role::System);
    $otherMessages = array_filter($messages, fn($m) => $m->role !== Role::System);

    // Keep only recent messages
    $recentMessages = array_slice($otherMessages, -$maxMessages);

    return array_merge($systemMessage, $recentMessages);
}
```

### Exercise 3: Response Formatting

Parse and format different types of responses:

````php
function parseCodeBlocks(string $content): array
{
    preg_match_all('/```(\w+)?\n(.*?)\n```/s', $content, $matches);
    return array_combine($matches[1], $matches[2]);
}
````

## Troubleshooting

### Issue: Inconsistent Response Quality

- **Solution**: Adjust temperature (0.0-1.0). Lower for consistency, higher for creativity
- Use more specific system prompts
- Provide examples in your prompts

### Issue: Context Length Exceeded

- **Solution**: Implement conversation trimming
- Summarize long conversations periodically
- Use smaller models for simple tasks

### Issue: Responses Cut Off

- **Solution**: Increase `maxTokens` parameter
- Break complex requests into smaller parts
- Check token usage in responses

### Issue: Off-Topic Responses

- **Solution**: Use clear system messages
- Be specific in your prompts
- Include examples of desired output format

## Next Steps

Ready to explore more advanced features? Check out:

1. **[03-chat-parameters](../03-chat-parameters)**: Fine-tune response generation with temperature, top_p, and more
2. **[04-streaming-chat](../04-streaming-chat)**: Handle real-time streaming responses
3. **[05-function-calling](../05-function-calling)**: Enable AI to call your PHP functions

### Further Reading

- [Mistral Chat Completion Guide](https://docs.mistral.ai/capabilities/completion)
- [Prompt Engineering Best Practices](https://docs.mistral.ai/guides/prompting)
- [Token Usage and Pricing](https://mistral.ai/pricing)

### Advanced Patterns

- **Chat Templates**: Create reusable conversation templates
- **Response Validation**: Implement JSON schema validation for structured outputs
- **Conversation Branching**: Save and restore conversation states
- **Multi-Model Routing**: Route queries to different models based on complexity

Remember: The quality of AI responses heavily depends on prompt clarity. Spend time crafting your system messages and
user prompts for optimal results!
