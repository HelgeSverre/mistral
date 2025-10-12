# Mastering Chat Parameters with Mistral PHP SDK

## Overview

This example deep-dives into the various parameters that control chat completion behavior. You'll learn how temperature,
top_p, max_tokens, and other settings affect response generation, and when to use each parameter for optimal results.
Understanding these parameters is crucial for building production-ready AI applications.

### Real-world Use Cases

- Fine-tuning creative writing assistants
- Controlling factual accuracy in Q&A systems
- Optimizing token usage for cost efficiency
- Ensuring consistent output formats
- Managing response length and verbosity

### Prerequisites

- Completed [02-basic-chat](../02-basic-chat) example
- Basic understanding of probability and sampling
- Familiarity with token concepts in LLMs

## Concepts

### Temperature: Creativity vs Determinism

Temperature controls randomness in response generation:

- **0.0**: Deterministic, most likely tokens chosen
- **0.3-0.7**: Balanced, good for most use cases
- **0.8-1.0**: Creative, more diverse outputs
- **>1.0**: Very random, potentially incoherent

### Top-p (Nucleus Sampling)

An alternative to temperature that considers cumulative probability:

- **0.1**: Very selective, only top 10% probability mass
- **0.9**: Default, good balance
- **1.0**: Consider all tokens

### Max Tokens

Controls the maximum length of generated responses:

- Prevents runaway generation
- Helps manage costs
- Ensures responses fit UI constraints

### Other Important Parameters

- **stop**: Custom stop sequences
- **presence_penalty**: Reduces repetition
- **frequency_penalty**: Encourages vocabulary diversity
- **seed**: For reproducible outputs

## Implementation

### Temperature Comparison

Let's see how temperature affects creativity:

```php
<?php

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Chat\ChatCompletionRequest;
use Helge\Mistral\Dto\Chat\ChatMessage;
use Helge\Mistral\Enums\Role;

function testTemperature(Mistral $client, float $temperature): string
{
    $request = ChatCompletionRequest::from([
        'model' => 'mistral-small-latest',
        'messages' => [
            ChatMessage::from([
                'role' => Role::User,
                'content' => 'Write a creative tagline for a coffee shop.',
            ]),
        ],
        'temperature' => $temperature,
        'maxTokens' => 50,
    ]);

    $response = $client->chat()->create($request);
    return $response->choices[0]->message->content;
}

$mistral = new Mistral($_ENV['MISTRAL_API_KEY']);

// Test different temperatures
$temperatures = [0.0, 0.5, 1.0, 1.5];
foreach ($temperatures as $temp) {
    echo "Temperature {$temp}: " . testTemperature($mistral, $temp) . "\n";
}
```

### Top-p vs Temperature

Understanding when to use each:

```php
class ParameterTester
{
    private Mistral $client;

    public function __construct(string $apiKey)
    {
        $this->client = new Mistral($apiKey);
    }

    public function compareApproaches(string $prompt): array
    {
        $results = [];

        // Approach 1: Low temperature (deterministic)
        $results['low_temp'] = $this->generate($prompt, [
            'temperature' => 0.2,
            'topP' => 1.0,
        ]);

        // Approach 2: High temperature (creative)
        $results['high_temp'] = $this->generate($prompt, [
            'temperature' => 0.9,
            'topP' => 1.0,
        ]);

        // Approach 3: Top-p sampling (nucleus)
        $results['top_p'] = $this->generate($prompt, [
            'temperature' => 1.0,
            'topP' => 0.9,
        ]);

        // Approach 4: Restrictive top-p
        $results['restrictive'] = $this->generate($prompt, [
            'temperature' => 1.0,
            'topP' => 0.5,
        ]);

        return $results;
    }

    private function generate(string $prompt, array $params): string
    {
        $request = ChatCompletionRequest::from([
            'model' => 'mistral-small-latest',
            'messages' => [
                ChatMessage::from([
                    'role' => Role::User,
                    'content' => $prompt,
                ]),
            ],
            'temperature' => $params['temperature'],
            'topP' => $params['topP'],
            'maxTokens' => 100,
        ]);

        $response = $this->client->chat()->create($request);
        return $response->choices[0]->message->content;
    }
}
```

### Managing Response Length

Control output size precisely:

```php
function generateWithLengthControl(
    Mistral $client,
    string $prompt,
    int $minLength = 50,
    int $maxLength = 200
): string {
    // First attempt with max tokens
    $request = ChatCompletionRequest::from([
        'model' => 'mistral-small-latest',
        'messages' => [
            ChatMessage::from([
                'role' => Role::System,
                'content' => "Provide responses between {$minLength} and {$maxLength} tokens.",
            ]),
            ChatMessage::from([
                'role' => Role::User,
                'content' => $prompt,
            ]),
        ],
        'maxTokens' => $maxLength,
        'temperature' => 0.7,
    ]);

    $response = $client->chat()->create($request);
    $content = $response->choices[0]->message->content;

    // Check if response meets minimum length
    $tokenCount = $response->usage->completionTokens;
    if ($tokenCount < $minLength) {
        // Request expansion
        $request->messages[] = ChatMessage::from([
            'role' => Role::User,
            'content' => 'Please expand your response with more detail.',
        ]);
        $response = $client->chat()->create($request);
        $content = $response->choices[0]->message->content;
    }

    return $content;
}
```

## Code Example

Complete working example (`chat-parameters.php`):

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

// Example 1: Temperature Effects
echo "=== Example 1: Temperature Effects ===\n\n";
echo "Prompt: 'Generate a product name for a smart water bottle'\n\n";

$temperatures = [0.0, 0.3, 0.7, 1.0, 1.5];
foreach ($temperatures as $temp) {
    $request = ChatCompletionRequest::from([
        'model' => 'mistral-small-latest',
        'messages' => [
            ChatMessage::from([
                'role' => Role::User,
                'content' => 'Generate a product name for a smart water bottle',
            ]),
        ],
        'temperature' => $temp,
        'maxTokens' => 20,
    ]);

    $response = $mistral->chat()->create($request);
    $name = trim($response->choices[0]->message->content);
    echo "Temperature {$temp}: {$name}\n";
}

// Example 2: Top-p Sampling
echo "\n=== Example 2: Top-p (Nucleus) Sampling ===\n\n";
echo "Prompt: 'Complete this sentence: The future of technology is...'\n\n";

$topPValues = [0.3, 0.5, 0.7, 0.9, 1.0];
foreach ($topPValues as $topP) {
    $request = ChatCompletionRequest::from([
        'model' => 'mistral-small-latest',
        'messages' => [
            ChatMessage::from([
                'role' => Role::User,
                'content' => 'Complete this sentence: The future of technology is',
            ]),
        ],
        'temperature' => 1.0,
        'topP' => $topP,
        'maxTokens' => 30,
    ]);

    $response = $mistral->chat()->create($request);
    $completion = trim($response->choices[0]->message->content);
    echo "Top-p {$topP}: ...{$completion}\n";
}

// Example 3: Max Tokens and Stop Sequences
echo "\n=== Example 3: Controlling Response Length ===\n\n";

$lengthTests = [
    ['maxTokens' => 10, 'label' => 'Very Short (10 tokens)'],
    ['maxTokens' => 50, 'label' => 'Short (50 tokens)'],
    ['maxTokens' => 150, 'label' => 'Medium (150 tokens)'],
];

foreach ($lengthTests as $test) {
    $request = ChatCompletionRequest::from([
        'model' => 'mistral-small-latest',
        'messages' => [
            ChatMessage::from([
                'role' => Role::User,
                'content' => 'Explain machine learning',
            ]),
        ],
        'maxTokens' => $test['maxTokens'],
        'temperature' => 0.7,
    ]);

    $response = $mistral->chat()->create($request);
    $content = $response->choices[0]->message->content;
    $tokenCount = $response->usage->completionTokens;

    echo "{$test['label']}:\n";
    echo "Response ({$tokenCount} tokens): " . substr($content, 0, 200) . "...\n\n";
}

// Example 4: Using Stop Sequences
echo "=== Example 4: Stop Sequences ===\n\n";

$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => [
        ChatMessage::from([
            'role' => Role::User,
            'content' => 'List 5 programming languages with brief descriptions:',
        ]),
    ],
    'temperature' => 0.5,
    'maxTokens' => 200,
    'stop' => ['6.', 'Thank you'], // Stop at "6." or "Thank you"
]);

$response = $mistral->chat()->create($request);
echo "List with stop sequence:\n";
echo $response->choices[0]->message->content . "\n\n";

// Example 5: Seed for Reproducibility
echo "=== Example 5: Reproducible Outputs with Seed ===\n\n";

$seed = 42;
echo "Using seed: {$seed}\n\n";

for ($i = 1; $i <= 3; $i++) {
    $request = ChatCompletionRequest::from([
        'model' => 'mistral-small-latest',
        'messages' => [
            ChatMessage::from([
                'role' => Role::User,
                'content' => 'Generate a random number between 1 and 100',
            ]),
        ],
        'temperature' => 1.0,
        'seed' => $seed,
        'maxTokens' => 20,
    ]);

    $response = $mistral->chat()->create($request);
    echo "Attempt {$i}: " . $response->choices[0]->message->content . "\n";
}

// Example 6: Parameter Combinations for Different Use Cases
echo "\n=== Example 6: Optimal Parameters by Use Case ===\n\n";

$useCases = [
    [
        'name' => 'Factual Q&A',
        'params' => ['temperature' => 0.0, 'topP' => 1.0],
        'prompt' => 'What is the boiling point of water?',
    ],
    [
        'name' => 'Creative Writing',
        'params' => ['temperature' => 0.9, 'topP' => 0.95],
        'prompt' => 'Write a opening line for a mystery novel',
    ],
    [
        'name' => 'Code Generation',
        'params' => ['temperature' => 0.3, 'topP' => 0.9],
        'prompt' => 'Write a PHP function to validate email',
    ],
    [
        'name' => 'Brainstorming',
        'params' => ['temperature' => 0.8, 'topP' => 0.9],
        'prompt' => 'Suggest startup ideas in healthcare',
    ],
];

foreach ($useCases as $useCase) {
    $request = ChatCompletionRequest::from([
        'model' => 'mistral-small-latest',
        'messages' => [
            ChatMessage::from([
                'role' => Role::User,
                'content' => $useCase['prompt'],
            ]),
        ],
        'temperature' => $useCase['params']['temperature'],
        'topP' => $useCase['params']['topP'],
        'maxTokens' => 100,
    ]);

    $response = $mistral->chat()->create($request);

    echo "{$useCase['name']}:\n";
    echo "  Params: temp={$useCase['params']['temperature']}, top_p={$useCase['params']['topP']}\n";
    echo "  Result: " . substr($response->choices[0]->message->content, 0, 150) . "...\n\n";
}

// Summary statistics
echo "=== Performance Summary ===\n";
echo "Note: Lower temperature = More deterministic\n";
echo "      Higher temperature = More creative/random\n";
echo "      Lower top-p = More focused vocabulary\n";
echo "      Higher top-p = More diverse vocabulary\n";
```

## Expected Output

You'll see varied outputs demonstrating parameter effects:

```
=== Example 1: Temperature Effects ===

Temperature 0.0: AquaSmart Pro
Temperature 0.3: HydroTrack Smart Bottle
Temperature 0.7: AquaPulse Intelligent Hydration
Temperature 1.0: NeptuneTech SmartFlow
Temperature 1.5: CrystalWave AquaGenius X

=== Example 2: Top-p (Nucleus) Sampling ===

Top-p 0.3: ...increasingly automated and efficient
Top-p 0.5: ...shaped by artificial intelligence
Top-p 0.7: ...interconnected and intelligent
Top-p 0.9: ...boundless and transformative
Top-p 1.0: ...surprisingly organic and human-centered

[Additional examples showing parameter effects...]
```

## Try It Yourself

### Exercise 1: Find Optimal Parameters

Test different parameter combinations for your use case:

```php
function findOptimalParams(Mistral $client, string $prompt, array $testCases): array
{
    $results = [];
    foreach ($testCases as $params) {
        // Test each combination multiple times
        $scores = [];
        for ($i = 0; $i < 3; $i++) {
            $response = /* generate with params */;
            $scores[] = evaluateResponse($response); // Your evaluation logic
        }
        $results[$params['label']] = array_sum($scores) / count($scores);
    }
    return $results;
}
```

### Exercise 2: Dynamic Parameter Adjustment

Adjust parameters based on context:

```php
function getOptimalParams(string $taskType): array
{
    return match($taskType) {
        'translation' => ['temperature' => 0.1, 'topP' => 1.0],
        'creative' => ['temperature' => 0.9, 'topP' => 0.95],
        'analytical' => ['temperature' => 0.3, 'topP' => 0.9],
        'brainstorm' => ['temperature' => 1.0, 'topP' => 0.8],
        default => ['temperature' => 0.7, 'topP' => 0.9],
    };
}
```

### Exercise 3: Cost Optimization

Balance quality with token usage:

```php
function optimizeForCost(Mistral $client, string $prompt, int $budget): string
{
    // Start with minimal tokens
    $maxTokens = 50;
    while ($maxTokens <= $budget) {
        $response = /* generate */;
        if (isResponseComplete($response)) {
            return $response;
        }
        $maxTokens += 50;
    }
}
```

## Troubleshooting

### Issue: Inconsistent Outputs

- **Solution**: Lower temperature for consistency
- Use seed parameter for exact reproducibility
- Implement response validation and retry logic

### Issue: Responses Too Short/Long

- **Solution**: Adjust maxTokens appropriately
- Use system prompts to guide length
- Implement stop sequences for natural endings

### Issue: Repetitive Content

- **Solution**: Increase temperature slightly
- Use presence_penalty and frequency_penalty
- Vary your prompts more

### Issue: Off-topic or Incoherent

- **Solution**: Reduce temperature below 1.0
- Use lower top-p values (0.7-0.9)
- Provide clearer instructions in prompts

## Next Steps

Now that you understand parameters, explore:

1. **[04-streaming-chat](../04-streaming-chat)**: Real-time streaming with these parameters
2. **[05-function-calling](../05-function-calling)**: Combine parameters with function calling
3. **[10-error-handling](../10-error-handling)**: Handle parameter-related errors

### Further Reading

- [Mistral API Parameters Guide](https://docs.mistral.ai/api/#operation/createChatCompletion)
- [Understanding Temperature and Top-p](https://docs.mistral.ai/concepts/sampling)
- [Token Optimization Strategies](https://docs.mistral.ai/guides/tokens)

### Best Practices

- **Start Conservative**: Begin with temperature 0.7 and adjust
- **Test Thoroughly**: Parameters affect different models differently
- **Document Settings**: Keep track of what works for each use case
- **Monitor Costs**: Higher maxTokens = higher costs
- **Use Defaults Wisely**: Mistral's defaults work well for most cases

Remember: There's no one-size-fits-all parameter configuration. Experiment to find what works best for your specific use
case!
