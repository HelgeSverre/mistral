# Function Calling with Mistral PHP SDK

## Overview

This example demonstrates Mistral's powerful function calling capability, allowing AI models to interact with your PHP
application by calling predefined functions. This enables the AI to fetch real-time data, perform calculations, interact
with databases, and trigger actions in your application, creating truly interactive AI assistants.

### Real-world Use Cases

- AI assistants that can query databases
- Chatbots that book appointments or check inventory
- Automated customer service with CRM integration
- Code execution and testing assistants
- Smart home control through natural language

### Prerequisites

- Completed [04-streaming-chat](../04-streaming-chat) example
- Understanding of JSON Schema
- Familiarity with PHP callable concepts
- Basic knowledge of API integration patterns

## Concepts

### Function Calling Flow

1. **Define Functions**: Specify available functions with JSON Schema
2. **User Query**: User asks something requiring function execution
3. **Model Decision**: AI determines which function(s) to call
4. **Function Execution**: Your code runs the requested function
5. **Result Integration**: AI incorporates results into its response

### Tool Definitions

Functions are defined as "tools" with:

- **Name**: Unique identifier for the function
- **Description**: What the function does (helps AI decide when to use it)
- **Parameters**: JSON Schema defining expected inputs
- **Required**: Which parameters are mandatory

### Execution Modes

- **Automatic**: AI decides when to call functions
- **Required**: Force the AI to use specific function(s)
- **None**: Disable function calling for specific requests

## Implementation

### Basic Function Definition

Define a simple function for the AI to use:

```php
<?php

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Chat\ChatCompletionRequest;
use Helge\Mistral\Dto\Chat\ChatMessage;
use Helge\Mistral\Dto\Chat\ToolCall;
use Helge\Mistral\Dto\Chat\FunctionTool;
use Helge\Mistral\Enums\Role;

// Define the function schema
$getCurrentWeather = FunctionTool::from([
    'type' => 'function',
    'function' => [
        'name' => 'get_current_weather',
        'description' => 'Get the current weather in a given location',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'location' => [
                    'type' => 'string',
                    'description' => 'The city and country, e.g. San Francisco, USA',
                ],
                'unit' => [
                    'type' => 'string',
                    'enum' => ['celsius', 'fahrenheit'],
                    'description' => 'Temperature unit',
                ],
            ],
            'required' => ['location'],
        ],
    ],
]);

// Function implementation
function get_current_weather(array $args): array
{
    $location = $args['location'];
    $unit = $args['unit'] ?? 'celsius';

    // Simulated weather data (in production, call a weather API)
    $weatherData = [
        'London, UK' => ['temp' => 15, 'condition' => 'cloudy'],
        'New York, USA' => ['temp' => 22, 'condition' => 'sunny'],
        'Tokyo, Japan' => ['temp' => 18, 'condition' => 'rainy'],
    ];

    $weather = $weatherData[$location] ?? ['temp' => 20, 'condition' => 'unknown'];

    if ($unit === 'fahrenheit') {
        $weather['temp'] = ($weather['temp'] * 9/5) + 32;
    }

    return [
        'location' => $location,
        'temperature' => $weather['temp'],
        'unit' => $unit,
        'condition' => $weather['condition'],
    ];
}
```

### Complete Function Calling Flow

Implement the full request-response cycle:

```php
class FunctionCallingAssistant
{
    private Mistral $client;
    private array $tools = [];
    private array $functions = [];

    public function __construct(string $apiKey)
    {
        $this->client = new Mistral($apiKey);
    }

    public function registerFunction(
        string $name,
        callable $implementation,
        array $schema
    ): void {
        $this->tools[] = FunctionTool::from([
            'type' => 'function',
            'function' => array_merge(['name' => $name], $schema),
        ]);
        $this->functions[$name] = $implementation;
    }

    public function chat(string $userMessage): string
    {
        $messages = [
            ChatMessage::from([
                'role' => Role::User,
                'content' => $userMessage,
            ]),
        ];

        // First API call - let AI decide if it needs to call functions
        $request = ChatCompletionRequest::from([
            'model' => 'mistral-small-latest',
            'messages' => $messages,
            'tools' => $this->tools,
            'toolChoice' => 'auto', // Let AI decide
        ]);

        $response = $this->client->chat()->create($request);
        $assistantMessage = $response->choices[0]->message;

        // Check if AI wants to call functions
        if (!empty($assistantMessage->toolCalls)) {
            $messages[] = $assistantMessage;

            // Execute each function call
            foreach ($assistantMessage->toolCalls as $toolCall) {
                $functionName = $toolCall->function->name;
                $arguments = json_decode($toolCall->function->arguments, true);

                // Execute the function
                $result = $this->executeFunction($functionName, $arguments);

                // Add function result to messages
                $messages[] = ChatMessage::from([
                    'role' => Role::Tool,
                    'content' => json_encode($result),
                    'toolCallId' => $toolCall->id,
                ]);
            }

            // Second API call - get final response with function results
            $request = ChatCompletionRequest::from([
                'model' => 'mistral-small-latest',
                'messages' => $messages,
            ]);

            $finalResponse = $this->client->chat()->create($request);
            return $finalResponse->choices[0]->message->content;
        }

        return $assistantMessage->content;
    }

    private function executeFunction(string $name, array $arguments): array
    {
        if (!isset($this->functions[$name])) {
            return ['error' => "Function {$name} not found"];
        }

        try {
            return $this->functions[$name]($arguments);
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
```

### Multiple Function Calls

Handle scenarios where AI needs to call multiple functions:

```php
// Register multiple functions
$assistant = new FunctionCallingAssistant($apiKey);

// Database query function
$assistant->registerFunction(
    'query_database',
    function(array $args) {
        $query = $args['query'];
        $table = $args['table'];

        // Simulate database query
        return [
            'results' => [
                ['id' => 1, 'name' => 'Product A', 'price' => 29.99],
                ['id' => 2, 'name' => 'Product B', 'price' => 49.99],
            ],
            'count' => 2,
        ];
    },
    [
        'description' => 'Query the database',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'table' => ['type' => 'string', 'description' => 'Table name'],
                'query' => ['type' => 'string', 'description' => 'SQL WHERE clause'],
            ],
            'required' => ['table'],
        ],
    ]
);

// Calculator function
$assistant->registerFunction(
    'calculate',
    function(array $args) {
        $expression = $args['expression'];
        // Safe evaluation (in production, use a proper math parser)
        $result = eval("return {$expression};");
        return ['result' => $result, 'expression' => $expression];
    },
    [
        'description' => 'Perform mathematical calculations',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'expression' => [
                    'type' => 'string',
                    'description' => 'Mathematical expression',
                ],
            ],
            'required' => ['expression'],
        ],
    ]
);

// Email sending function
$assistant->registerFunction(
    'send_email',
    function(array $args) {
        // Simulate email sending
        return [
            'status' => 'sent',
            'to' => $args['to'],
            'subject' => $args['subject'],
            'messageId' => uniqid('email_'),
        ];
    },
    [
        'description' => 'Send an email',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'to' => ['type' => 'string'],
                'subject' => ['type' => 'string'],
                'body' => ['type' => 'string'],
            ],
            'required' => ['to', 'subject', 'body'],
        ],
    ]
);
```

## Code Example

Complete working example (`function-calling.php`):

```php
<?php

require_once 'vendor/autoload.php';

use Helge\Mistral\Mistral;
use Helge\Mistral\Dto\Chat\ChatCompletionRequest;
use Helge\Mistral\Dto\Chat\ChatMessage;
use Helge\Mistral\Dto\Chat\FunctionTool;
use Helge\Mistral\Enums\Role;

$apiKey = $_ENV['MISTRAL_API_KEY'] ?? getenv('MISTRAL_API_KEY');
if (!$apiKey) {
    die("Please set your MISTRAL_API_KEY environment variable\n");
}

$mistral = new Mistral($apiKey);

// Example 1: Weather Function
echo "=== Example 1: Weather Query ===\n\n";

// Define weather function
$weatherTool = FunctionTool::from([
    'type' => 'function',
    'function' => [
        'name' => 'get_weather',
        'description' => 'Get current weather for a location',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'location' => [
                    'type' => 'string',
                    'description' => 'City and country',
                ],
                'detailed' => [
                    'type' => 'boolean',
                    'description' => 'Include detailed forecast',
                ],
            ],
            'required' => ['location'],
        ],
    ],
]);

// Implementation
function get_weather(array $args): array
{
    $location = $args['location'];
    $detailed = $args['detailed'] ?? false;

    $data = [
        'location' => $location,
        'temperature' => rand(10, 30),
        'condition' => ['sunny', 'cloudy', 'rainy'][rand(0, 2)],
        'humidity' => rand(40, 80),
    ];

    if ($detailed) {
        $data['forecast'] = [
            'tomorrow' => ['high' => rand(15, 35), 'low' => rand(5, 20)],
            'day_after' => ['high' => rand(15, 35), 'low' => rand(5, 20)],
        ];
    }

    return $data;
}

// User query
$messages = [
    ChatMessage::from([
        'role' => Role::User,
        'content' => "What's the weather like in Paris, France? Give me details.",
    ]),
];

// First call - AI decides to use function
$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => $messages,
    'tools' => [$weatherTool],
    'toolChoice' => 'auto',
]);

$response = $mistral->chat()->create($request);
$assistantMessage = $response->choices[0]->message;

echo "User: What's the weather like in Paris, France? Give me details.\n\n";

if (!empty($assistantMessage->toolCalls)) {
    echo "AI decided to call: " . $assistantMessage->toolCalls[0]->function->name . "\n";
    $args = json_decode($assistantMessage->toolCalls[0]->function->arguments, true);
    echo "With arguments: " . json_encode($args, JSON_PRETTY_PRINT) . "\n\n";

    // Execute function
    $result = get_weather($args);
    echo "Function returned: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

    // Add to conversation
    $messages[] = $assistantMessage;
    $messages[] = ChatMessage::from([
        'role' => Role::Tool,
        'content' => json_encode($result),
        'toolCallId' => $assistantMessage->toolCalls[0]->id,
    ]);

    // Get final response
    $finalRequest = ChatCompletionRequest::from([
        'model' => 'mistral-small-latest',
        'messages' => $messages,
    ]);

    $finalResponse = $mistral->chat()->create($finalRequest);
    echo "Final response: " . $finalResponse->choices[0]->message->content . "\n\n";
}

// Example 2: Database Operations
echo "=== Example 2: Database Query ===\n\n";

$databaseTool = FunctionTool::from([
    'type' => 'function',
    'function' => [
        'name' => 'database_query',
        'description' => 'Query product database',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['search', 'count', 'get_by_id'],
                ],
                'criteria' => [
                    'type' => 'object',
                    'description' => 'Search criteria',
                ],
            ],
            'required' => ['action'],
        ],
    ],
]);

function database_query(array $args): array
{
    $action = $args['action'];
    $criteria = $args['criteria'] ?? [];

    // Simulated database
    $products = [
        ['id' => 1, 'name' => 'Laptop', 'price' => 999, 'category' => 'Electronics'],
        ['id' => 2, 'name' => 'Mouse', 'price' => 29, 'category' => 'Electronics'],
        ['id' => 3, 'name' => 'Coffee Maker', 'price' => 89, 'category' => 'Appliances'],
    ];

    switch ($action) {
        case 'search':
            $filtered = array_filter($products, function($p) use ($criteria) {
                foreach ($criteria as $key => $value) {
                    if (!isset($p[$key]) || $p[$key] != $value) {
                        return false;
                    }
                }
                return true;
            });
            return ['results' => array_values($filtered)];

        case 'count':
            return ['total' => count($products)];

        case 'get_by_id':
            $id = $criteria['id'] ?? 0;
            $product = array_filter($products, fn($p) => $p['id'] == $id);
            return ['product' => array_values($product)[0] ?? null];

        default:
            return ['error' => 'Unknown action'];
    }
}

$messages = [
    ChatMessage::from([
        'role' => Role::User,
        'content' => 'Find all electronic products in our database',
    ]),
];

$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => $messages,
    'tools' => [$databaseTool],
]);

$response = $mistral->chat()->create($request);
echo "Query: Find all electronic products\n";

// Process function calls
if (!empty($response->choices[0]->message->toolCalls)) {
    foreach ($response->choices[0]->message->toolCalls as $toolCall) {
        $args = json_decode($toolCall->function->arguments, true);
        $result = database_query($args);
        echo "Database returned: " . count($result['results'] ?? []) . " products\n";

        foreach ($result['results'] ?? [] as $product) {
            echo "  - {$product['name']}: \${$product['price']}\n";
        }
    }
}

echo "\n";

// Example 3: Calculation Assistant
echo "=== Example 3: Math Calculations ===\n\n";

$calculatorTool = FunctionTool::from([
    'type' => 'function',
    'function' => [
        'name' => 'calculate',
        'description' => 'Perform mathematical calculations',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'operation' => [
                    'type' => 'string',
                    'enum' => ['add', 'subtract', 'multiply', 'divide', 'power', 'sqrt'],
                ],
                'values' => [
                    'type' => 'array',
                    'items' => ['type' => 'number'],
                    'description' => 'Values to operate on',
                ],
            ],
            'required' => ['operation', 'values'],
        ],
    ],
]);

function calculate(array $args): array
{
    $operation = $args['operation'];
    $values = $args['values'];

    try {
        $result = match($operation) {
            'add' => array_sum($values),
            'subtract' => $values[0] - ($values[1] ?? 0),
            'multiply' => array_product($values),
            'divide' => $values[0] / ($values[1] ?: 1),
            'power' => pow($values[0], $values[1] ?? 2),
            'sqrt' => sqrt($values[0]),
            default => throw new Exception("Unknown operation"),
        };

        return [
            'operation' => $operation,
            'values' => $values,
            'result' => $result,
        ];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

$messages = [
    ChatMessage::from([
        'role' => Role::User,
        'content' => 'What is 15 squared plus the square root of 144?',
    ]),
];

$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => $messages,
    'tools' => [$calculatorTool],
]);

echo "Question: What is 15 squared plus the square root of 144?\n\n";

$response = $mistral->chat()->create($request);
$assistantMessage = $response->choices[0]->message;

// AI might make multiple function calls
if (!empty($assistantMessage->toolCalls)) {
    $messages[] = $assistantMessage;

    echo "AI is calculating:\n";
    foreach ($assistantMessage->toolCalls as $toolCall) {
        $args = json_decode($toolCall->function->arguments, true);
        $result = calculate($args);

        echo "  {$args['operation']}(" . implode(', ', $args['values']) . ") = {$result['result']}\n";

        $messages[] = ChatMessage::from([
            'role' => Role::Tool,
            'content' => json_encode($result),
            'toolCallId' => $toolCall->id,
        ]);
    }

    // Get final answer
    $finalRequest = ChatCompletionRequest::from([
        'model' => 'mistral-small-latest',
        'messages' => $messages,
    ]);

    $finalResponse = $mistral->chat()->create($finalRequest);
    echo "\nFinal answer: " . $finalResponse->choices[0]->message->content . "\n";
}

// Example 4: Forced Function Usage
echo "\n=== Example 4: Force Function Call ===\n\n";

$messages = [
    ChatMessage::from([
        'role' => Role::User,
        'content' => 'Hello, how are you?',
    ]),
];

$request = ChatCompletionRequest::from([
    'model' => 'mistral-small-latest',
    'messages' => $messages,
    'tools' => [$weatherTool],
    'toolChoice' => 'any', // Force use of a tool even if not needed
]);

echo "User: Hello, how are you?\n";
echo "Forcing AI to use weather tool...\n\n";

$response = $mistral->chat()->create($request);

if (!empty($response->choices[0]->message->toolCalls)) {
    $functionCall = $response->choices[0]->message->toolCalls[0];
    echo "AI called: {$functionCall->function->name}\n";
    echo "Even though it wasn't necessary for the query!\n";
}

echo "\n=== Summary ===\n";
echo "Function calling enables AI to:\n";
echo "1. Access real-time data\n";
echo "2. Perform calculations\n";
echo "3. Interact with databases\n";
echo "4. Trigger application actions\n";
echo "5. Create truly interactive assistants\n";
```

## Expected Output

```
=== Example 1: Weather Query ===

User: What's the weather like in Paris, France? Give me details.

AI decided to call: get_weather
With arguments: {
    "location": "Paris, France",
    "detailed": true
}

Function returned: {
    "location": "Paris, France",
    "temperature": 18,
    "condition": "cloudy",
    "humidity": 65,
    "forecast": {
        "tomorrow": {"high": 22, "low": 14},
        "day_after": {"high": 24, "low": 16}
    }
}

Final response: The current weather in Paris, France is cloudy with a temperature
of 18°C and 65% humidity. Looking ahead, tomorrow will see a high of 22°C and a
low of 14°C, while the day after will be slightly warmer with a high of 24°C.

[Additional examples follow...]
```

## Try It Yourself

### Exercise 1: Build a Smart Assistant

Create an assistant with multiple capabilities:

```php
class SmartAssistant {
    private array $tools = [];
    private Mistral $client;

    public function addCapability(string $name, callable $handler, array $schema): void
    {
        // Register tools dynamically
    }

    public function process(string $query): string
    {
        // Handle complex multi-step operations
    }
}
```

### Exercise 2: Function Chaining

Enable the AI to chain function calls:

```php
function processWithChaining(array $messages, array $tools): array
{
    $maxIterations = 5;
    $iteration = 0;

    while ($iteration < $maxIterations) {
        // Keep calling functions until AI stops requesting them
        $response = $client->chat()->create(/* request */);

        if (empty($response->choices[0]->message->toolCalls)) {
            break;
        }

        // Process function calls and continue
        $iteration++;
    }

    return $messages;
}
```

### Exercise 3: Parallel Function Execution

Execute multiple functions concurrently:

```php
function executeParallel(array $toolCalls): array
{
    $promises = [];
    foreach ($toolCalls as $call) {
        $promises[$call->id] = async(function() use ($call) {
            return executeFunction($call);
        });
    }
    return await($promises);
}
```

## Troubleshooting

### Issue: AI Not Calling Functions

- **Solution**: Make descriptions clear and specific
- Ensure parameter schemas are correct
- Try setting `toolChoice` to 'any' or specify function

### Issue: Invalid Function Arguments

- **Solution**: Validate arguments before execution
- Provide better parameter descriptions
- Include examples in the schema

### Issue: Function Execution Errors

- **Solution**: Wrap function calls in try-catch
- Return error messages for AI to handle
- Log failures for debugging

### Issue: Infinite Function Loops

- **Solution**: Implement iteration limits
- Track function call history
- Add circuit breakers

## Next Steps

Continue learning with:

1. **[06-embeddings](../06-embeddings)**: Generate and use text embeddings
2. **[04-streaming-chat](../04-streaming-chat)**: Combine streaming with functions
3. **[10-error-handling](../10-error-handling)**: Handle function errors gracefully

### Further Reading

- [Mistral Function Calling Guide](https://docs.mistral.ai/capabilities/function_calling)
- [JSON Schema Documentation](https://json-schema.org/)
- [OpenAPI Specification](https://swagger.io/specification/)

### Advanced Patterns

- **RAG Integration**: Use functions to query vector databases
- **Workflow Automation**: Chain functions for complex workflows
- **API Gateway**: Use functions as a bridge to external APIs
- **State Management**: Maintain context across function calls
- **Security**: Implement authorization for sensitive functions

Remember: Function calling transforms AI from a text generator into an intelligent agent that can interact with your
application. Design your functions carefully and always validate inputs!
