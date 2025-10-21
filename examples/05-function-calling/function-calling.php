<?php

/**
 * Function Calling (Tool Use)
 *
 * Description: Enable Mistral AI to call external functions and tools
 * Use Case: Building agentic systems, API integrations, dynamic data retrieval
 * Prerequisites: MISTRAL_API_KEY in .env file
 *
 * @see https://docs.mistral.ai/capabilities/function_calling/
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
    displayTitle('Function Calling (Tool Use)', '🔧');

    $mistral = createMistralClient();

    try {
        // Example 1: Simple function calling
        simpleFunctionCalling($mistral);

        // Example 2: Multiple functions
        multipleFunctions($mistral);

        // Example 3: Real-world example - Weather assistant
        weatherAssistant($mistral);

    } catch (Throwable $e) {
        handleError($e);
    }
}

/**
 * Example 1: Basic function calling with a single tool
 */
function simpleFunctionCalling(Mistral $mistral): void
{
    displaySection('Example 1: Simple Function Calling');
    echo "Using a simple calculator function...\n\n";

    // Step 1: Define the function/tool
    // Tools are defined using JSON Schema format
    $tools = [
        [
            'type' => 'function',
            'function' => [
                'name' => 'calculate',
                'description' => 'Perform basic arithmetic calculations (add, subtract, multiply, divide)',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'operation' => [
                            'type' => 'string',
                            'enum' => ['add', 'subtract', 'multiply', 'divide'],
                            'description' => 'The arithmetic operation to perform',
                        ],
                        'a' => [
                            'type' => 'number',
                            'description' => 'The first number',
                        ],
                        'b' => [
                            'type' => 'number',
                            'description' => 'The second number',
                        ],
                    ],
                    'required' => ['operation', 'a', 'b'],
                ],
            ],
        ],
    ];

    // Step 2: Send a message that requires the function
    $messages = [
        [
            'role' => Role::user->value,
            'content' => 'What is 42 multiplied by 17?',
        ],
    ];

    echo "🧑 User: {$messages[0]['content']}\n\n";

    // Step 3: Make the API call with tools
    $response = $mistral->chat()->create(
        messages: $messages,
        model: Model::large->value,
        tools: $tools,
        toolChoice: 'auto', // Let the model decide when to use tools
        maxTokens: 500,
    );

    $dto = $response->dtoOrFail();
    $choice = $dto->choices->first();
    if (! $choice) {
        echo "❌ No response received\n\n";

        return;
    }

    // Step 4: Check if the model wants to call a function
    if (! empty($choice->message->toolCalls)) {
        $toolCall = $choice->message->toolCalls[0];

        echo "🤖 Model wants to call function: {$toolCall->function->name}\n";
        echo "📋 Function arguments:\n";
        $functionArgs = json_decode($toolCall->function->arguments, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo '❌ JSON decode error: '.json_last_error_msg()."\n\n";

            return;
        }
        printJson($functionArgs, 'Arguments');

        // Step 5: Execute the function
        $result = executeCalculate($functionArgs);

        echo "✅ Function executed: {$result}\n\n";

        // Step 6: Send the function result back to the model
        $messages[] = [
            'role' => Role::assistant->value,
            'content' => '',
            'tool_calls' => [[
                'id' => $toolCall->id,
                'type' => 'function',
                'function' => [
                    'name' => $toolCall->function->name,
                    'arguments' => $toolCall->function->arguments,
                ],
            ]],
        ];

        $messages[] = [
            'role' => 'tool',
            'tool_call_id' => $toolCall->id,
            'content' => (string) $result,
        ];

        // Step 7: Get the final response
        $finalResponse = $mistral->chat()->create(
            messages: $messages,
            model: Model::large->value,
            tools: $tools,
            maxTokens: 200,
        );

        $finalDto = $finalResponse->dtoOrFail();
        $finalChoice = $finalDto->choices->first();
        if ($finalChoice) {
            echo "🤖 Assistant final response:\n";
            echo $finalChoice->message->content."\n\n";
        } else {
            echo "❌ No final response received\n\n";
        }
    } else {
        echo "🤖 Assistant: {$choice->message->content}\n\n";
    }

    echo "💡 Function Calling Flow:\n";
    echo "  1. Define functions with JSON Schema\n";
    echo "  2. Send message with tools parameter\n";
    echo "  3. Check for tool_calls in response\n";
    echo "  4. Execute the requested function\n";
    echo "  5. Send results back to model\n";
    echo "  6. Get final formatted response\n\n";
}

/**
 * Example 2: Multiple functions - let the model choose
 */
function multipleFunctions(Mistral $mistral): void
{
    displaySection('Example 2: Multiple Functions');
    echo "Providing multiple tools for the model to choose from...\n\n";

    // Define multiple functions
    $tools = [
        [
            'type' => 'function',
            'function' => [
                'name' => 'get_current_time',
                'description' => 'Get the current time in a specific timezone',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'timezone' => [
                            'type' => 'string',
                            'description' => 'The timezone (e.g., "America/New_York", "Europe/London")',
                        ],
                    ],
                    'required' => ['timezone'],
                ],
            ],
        ],
        [
            'type' => 'function',
            'function' => [
                'name' => 'convert_currency',
                'description' => 'Convert an amount from one currency to another',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'amount' => [
                            'type' => 'number',
                            'description' => 'The amount to convert',
                        ],
                        'from_currency' => [
                            'type' => 'string',
                            'description' => 'Source currency code (e.g., USD, EUR)',
                        ],
                        'to_currency' => [
                            'type' => 'string',
                            'description' => 'Target currency code (e.g., USD, EUR)',
                        ],
                    ],
                    'required' => ['amount', 'from_currency', 'to_currency'],
                ],
            ],
        ],
        [
            'type' => 'function',
            'function' => [
                'name' => 'search_database',
                'description' => 'Search a database for specific records',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => [
                            'type' => 'string',
                            'description' => 'The search query',
                        ],
                        'table' => [
                            'type' => 'string',
                            'description' => 'The database table to search',
                        ],
                    ],
                    'required' => ['query', 'table'],
                ],
            ],
        ],
    ];

    // Test different queries that require different functions
    $queries = [
        'What time is it in Tokyo?',
        'Convert 100 USD to EUR',
        'Search the users table for anyone named John',
    ];

    foreach ($queries as $query) {
        echo "🧑 User: {$query}\n";

        $messages = [
            ['role' => Role::user->value, 'content' => $query],
        ];

        $response = $mistral->chat()->create(
            messages: $messages,
            model: Model::large->value,
            tools: $tools,
            toolChoice: 'auto',
            maxTokens: 500,
        );

        $dto = $response->dtoOrFail();
        $choice = $dto->choices->first();
        if (! $choice) {
            echo "❌ No response received\n\n";

            continue;
        }

        if (! empty($choice->message->toolCalls)) {
            $toolCall = $choice->message->toolCalls[0];
            echo "🔧 Selected function: {$toolCall->function->name}\n";
            $args = json_decode($toolCall->function->arguments, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo '❌ JSON decode error: '.json_last_error_msg()."\n\n";

                continue;
            }
            echo '📋 Arguments: '.json_encode($args)."\n\n";
        } else {
            echo "💬 No function call needed\n";
            echo "🤖 Response: {$choice->message->content}\n\n";
        }

        echo str_repeat('─', 40)."\n";
    }

    echo "\n💡 Multiple Functions Best Practices:\n";
    echo "  • Provide clear, distinct function descriptions\n";
    echo "  • Use descriptive parameter names\n";
    echo "  • Let the model choose with tool_choice='auto'\n";
    echo "  • Handle cases where no function is needed\n";
    echo "  • Validate function arguments before execution\n\n";
}

/**
 * Example 3: Real-world weather assistant
 */
function weatherAssistant(Mistral $mistral): void
{
    displaySection('Example 3: Weather Assistant');
    echo "Building a practical weather information assistant...\n\n";

    // Define weather-related functions
    $tools = [
        [
            'type' => 'function',
            'function' => [
                'name' => 'get_current_weather',
                'description' => 'Get the current weather conditions for a location',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'The city and country, e.g., "Paris, France"',
                        ],
                        'units' => [
                            'type' => 'string',
                            'enum' => ['celsius', 'fahrenheit'],
                            'description' => 'Temperature units',
                        ],
                    ],
                    'required' => ['location'],
                ],
            ],
        ],
        [
            'type' => 'function',
            'function' => [
                'name' => 'get_weather_forecast',
                'description' => 'Get weather forecast for the next N days',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'The city and country',
                        ],
                        'days' => [
                            'type' => 'integer',
                            'description' => 'Number of days for forecast (1-7)',
                            'minimum' => 1,
                            'maximum' => 7,
                        ],
                    ],
                    'required' => ['location', 'days'],
                ],
            ],
        ],
    ];

    // Simulate a conversation
    $conversation = [
        [
            'role' => Role::user->value,
            'content' => 'What is the weather like in Bergen, Norway?',
        ],
    ];

    echo "🧑 User: {$conversation[0]['content']}\n\n";

    // First API call
    $response = $mistral->chat()->create(
        messages: $conversation,
        model: Model::large->value,
        tools: $tools,
        maxTokens: 500,
    );

    $dto = $response->dtoOrFail();
    $choice = $dto->choices->first();
    if (! $choice) {
        echo "❌ No response received\n\n";

        return;
    }

    if (! empty($choice->message->toolCalls)) {
        $toolCall = $choice->message->toolCalls[0];

        echo "🔧 Function called: {$toolCall->function->name}\n";
        $args = json_decode($toolCall->function->arguments, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo '❌ JSON decode error: '.json_last_error_msg()."\n\n";

            return;
        }
        printJson($args, 'Function Arguments');

        // Execute the function (simulated)
        $weatherData = executeGetCurrentWeather($args);

        echo "✅ Function result:\n";
        printJson($weatherData, 'Weather Data');

        // Add assistant's function call to conversation
        $conversation[] = [
            'role' => Role::assistant->value,
            'content' => '',
            'tool_calls' => [[
                'id' => $toolCall->id,
                'type' => 'function',
                'function' => [
                    'name' => $toolCall->function->name,
                    'arguments' => $toolCall->function->arguments,
                ],
            ]],
        ];

        // Add function result
        $conversation[] = [
            'role' => 'tool',
            'tool_call_id' => $toolCall->id,
            'content' => json_encode($weatherData),
        ];

        // Get final formatted response
        $finalResponse = $mistral->chat()->create(
            messages: $conversation,
            model: Model::large->value,
            tools: $tools,
            maxTokens: 300,
        );

        $finalDto = $finalResponse->dtoOrFail();
        $finalChoice = $finalDto->choices->first();
        if ($finalChoice) {
            echo "🤖 Assistant:\n";
            echo $finalChoice->message->content."\n\n";
        } else {
            echo "❌ No final response received\n\n";
        }
    }

    echo "💡 Production Considerations:\n";
    echo "  • Implement actual API calls to weather services\n";
    echo "  • Add error handling for API failures\n";
    echo "  • Cache results to reduce API calls\n";
    echo "  • Validate location names before API calls\n";
    echo "  • Handle rate limits gracefully\n";
    echo "  • Log function calls for debugging\n";
    echo "  • Set timeouts for external API calls\n\n";

    echo "🔒 Security Notes:\n";
    echo "  • Validate all function arguments\n";
    echo "  • Sanitize user input before function execution\n";
    echo "  • Use allowlists for function parameters\n";
    echo "  • Never execute arbitrary code from arguments\n";
    echo "  • Implement rate limiting on function calls\n";
    echo "  • Log and monitor function usage\n";
}

/**
 * Execute calculator function (helper)
 */
function executeCalculate(array $args): float|int
{
    if (! isset($args['a'], $args['b'], $args['operation'])) {
        throw new InvalidArgumentException('Missing required arguments: a, b, operation');
    }

    if (! is_numeric($args['a']) || ! is_numeric($args['b'])) {
        throw new InvalidArgumentException('Arguments a and b must be numeric');
    }

    $a = $args['a'];
    $b = $args['b'];
    $operation = $args['operation'];

    return match ($operation) {
        'add' => $a + $b,
        'subtract' => $a - $b,
        'multiply' => $a * $b,
        'divide' => $b != 0 ? $a / $b : throw new InvalidArgumentException('Division by zero'),
        default => throw new InvalidArgumentException("Unknown operation: {$operation}"),
    };
}

/**
 * Execute get_current_weather function (simulated)
 */
function executeGetCurrentWeather(array $args): array
{
    if (! isset($args['location'])) {
        throw new InvalidArgumentException('Missing required argument: location');
    }

    if (! is_string($args['location']) || empty(trim($args['location']))) {
        throw new InvalidArgumentException('Location must be a non-empty string');
    }

    // In production, this would call a real weather API
    return [
        'location' => $args['location'],
        'temperature' => 12,
        'units' => $args['units'] ?? 'celsius',
        'conditions' => 'Rainy',
        'humidity' => 85,
        'wind_speed' => 15,
        'timestamp' => date('Y-m-d H:i:s'),
    ];
}

// Run the example if executed directly
if (php_sapi_name() === 'cli') {
    main();
}
