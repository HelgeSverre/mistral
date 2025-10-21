# Getting Started with Mistral PHP SDK

## Overview

Welcome to the Mistral PHP SDK! This example demonstrates how to install the SDK, configure authentication, and make
your first API call. By the end of this tutorial, you'll have a working setup that can communicate with Mistral's AI
models and understand the fundamental patterns used throughout the SDK.

### Real-world Use Cases

- Setting up a chatbot for customer service
- Integrating AI-powered text generation into Laravel applications
- Building content generation tools
- Creating AI assistants for PHP applications

### Prerequisites

- PHP 8.1 or higher
- Composer installed
- A Mistral AI API key (get one at [console.mistral.ai](https://console.mistral.ai))
- Basic understanding of PHP and Composer

## Concepts

### Mistral AI Platform

Mistral AI provides powerful language models through a REST API. The PHP SDK wraps these API endpoints in a
developer-friendly interface, handling authentication, request formatting, and response parsing automatically.

### SDK Architecture

The Mistral PHP SDK is built on top of Saloon, a powerful HTTP client for PHP. It follows these key patterns:

- **Connector Pattern**: The main `Mistral` class acts as your gateway to all API resources
- **Resource Pattern**: Each API feature (chat, embeddings, etc.) has its own resource class
- **DTO Pattern**: Data Transfer Objects ensure type safety and IDE autocomplete

### Authentication

Mistral uses API key authentication. Your API key should be kept secret and never committed to version control.

## Implementation

### Step 1: Installation

Install the SDK using Composer:

```bash
composer require helgesverre/mistral
```

### Step 2: Environment Configuration

Create a `.env` file in your project root (add it to `.gitignore`!):

```env
MISTRAL_API_KEY=your-api-key-here
```

### Step 3: Basic Setup

Create a new PHP file to test your setup:

```php
<?php

require_once 'vendor/autoload.php';

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Chat\ChatCompletionRequest;
use Helge\Mistral\Dto\Chat\ChatMessage;
use Helge\Mistral\Enums\Role;

// Load environment variables (optional, if using vlucas/phpdotenv)
if (file_exists('.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Initialize the client
$apiKey = $_ENV['MISTRAL_API_KEY'] ?? getenv('MISTRAL_API_KEY');
if (!$apiKey) {
    die("Please set your MISTRAL_API_KEY environment variable\n");
}

$mistral = new Mistral($apiKey);
```

### Step 4: Your First API Call

Let's make a simple chat completion request:

```php
// Create a chat request
$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => 'Hello, Mistral! Please introduce yourself.',
        ]),
    ],
]);

// Send the request
try {
    $dto = $mistral->chat()->createDto($request);

    // Display the response
    echo "Mistral says: " . $dto->choices[0]->message->content . "\n";

    // Show token usage
    echo "\nTokens used:\n";
    echo "- Prompt: {$dto->usage->promptTokens}\n";
    echo "- Completion: {$dto->usage->completionTokens}\n";
    echo "- Total: {$dto->usage->totalTokens}\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Code Example

Here's the complete working example (`getting-started.php`):

```php
<?php

require_once 'vendor/autoload.php';

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Chat\ChatCompletionRequest;
use Helge\Mistral\Dto\Chat\ChatMessage;
use Helge\Mistral\Enums\Role;

// Get API key from environment
$apiKey = $_ENV['MISTRAL_API_KEY'] ?? getenv('MISTRAL_API_KEY');
if (!$apiKey) {
    die("Please set your MISTRAL_API_KEY environment variable\n");
}

// Initialize Mistral client
$mistral = new Mistral($apiKey);

// Create a simple chat request
$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => 'What is the capital of France? Answer in one sentence.',
        ]),
    ],
    'temperature' => 0.7,
    'maxTokens' => 100,
]);

try {
    // Send request to Mistral API
    $dto = $mistral->chat()->createDto($request);

    // Extract the assistant's response
    $assistantMessage = $dto->choices[0]->message->content;

    // Display results
    echo "Question: What is the capital of France?\n";
    echo "Answer: {$assistantMessage}\n";
    echo "\n--- Statistics ---\n";
    echo "Model: {$dto->model}\n";
    echo "Tokens used: {$dto->usage->totalTokens}\n";
    echo "Response time: " . round($dto->usage->completionTime ?? 0, 2) . "s\n";

} catch (Exception $e) {
    echo "Error occurred: " . $e->getMessage() . "\n";

    // Check for common issues
    if (strpos($e->getMessage(), '401') !== false) {
        echo "Authentication failed. Please check your API key.\n";
    } elseif (strpos($e->getMessage(), '429') !== false) {
        echo "Rate limit exceeded. Please wait and try again.\n";
    }
}
```

## Expected Output

When you run the example, you should see output similar to:

```
Question: What is the capital of France?
Answer: The capital of France is Paris.

--- Statistics ---
Model: mistral-small-latest
Tokens used: 28
Response time: 0.45s
```

### Understanding the Response

The response object contains:

- `choices`: An array of possible completions (usually just one)
- `usage`: Token consumption details for billing
- `model`: The actual model used
- `created`: Timestamp of the response

## Try It Yourself

### Exercise 1: Change the Model

Try using different models:

```php
'model' => 'mistral-tiny',  // Faster, more economical
'model' => 'mistral-medium', // Balance of speed and quality
'model' => 'mistral-large-latest', // Most capable
```

### Exercise 2: Multi-turn Conversation

Add a follow-up message:

```php
'messages' => [
    ChatMessage::from([
        'role' => Role::User,
        'content' => 'What is the capital of France?',
    ]),
    ChatMessage::from([
        'role' => Role::Assistant,
        'content' => 'The capital of France is Paris.',
    ]),
    ChatMessage::from([
        'role' => Role::User,
        'content' => 'What is its population?',
    ]),
],
```

### Exercise 3: List Available Models

Explore available models:

```php
$dto = $mistral->models()->listDto();
foreach ($dto->data as $model) {
    echo "Model: {$model->id}\n";
    echo "  Created: " . date('Y-m-d', $model->created) . "\n";
}
```

## Troubleshooting

### Common Issues and Solutions

**Issue: "MISTRAL_API_KEY not set"**

- Solution: Ensure your `.env` file exists and contains `MISTRAL_API_KEY=your-key-here`
- Alternative: Set it directly in your shell: `export MISTRAL_API_KEY=your-key-here`

**Issue: 401 Unauthorized**

- Your API key might be invalid or expired
- Check your key at [console.mistral.ai](https://console.mistral.ai)
- Ensure there are no extra spaces in your API key

**Issue: 429 Rate Limit Exceeded**

- You've hit the API rate limits
- Wait a few seconds before retrying
- Consider implementing exponential backoff (see example 10-error-handling)

**Issue: Connection Timeout**

- Check your internet connection
- Mistral's API might be experiencing issues (check status.mistral.ai)
- Try increasing the timeout in the client configuration

**Issue: Class Not Found**

- Run `composer dump-autoload` to regenerate autoloader
- Ensure you've run `composer install` after adding the package

## Next Steps

Now that you have a working setup, explore these related examples:

1. **[02-basic-chat](../02-basic-chat)**: Learn about different chat patterns and system messages
2. **[03-chat-parameters](../03-chat-parameters)**: Master temperature, top_p, and other parameters
3. **[10-error-handling](../10-error-handling)**: Implement robust error handling

### Further Reading

- [Mistral AI Documentation](https://docs.mistral.ai)
- [Saloon PHP Documentation](https://docs.saloon.dev)
- [PHP Environment Variables Best Practices](https://www.php.net/manual/en/reserved.variables.environment.php)

### Advanced Techniques

- Use dependency injection containers for the Mistral client
- Implement a service provider in Laravel
- Create a configuration class for different environments
- Add logging with Monolog or Laravel's Log facade

Remember: Never commit your API keys to version control. Always use environment variables or secure key management
systems in production!
