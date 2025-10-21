<p align="center"><img src="./art/header.png"></p>

# Laravel Client for Mistral.AI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/helgesverre/mistral.svg?style=flat-square)](https://packagist.org/packages/helgesverre/mistral)
[![Total Downloads](https://img.shields.io/packagist/dt/helgesverre/mistral.svg?style=flat-square)](https://packagist.org/packages/helgesverre/mistral)

The Mistral.ai Laravel Client enables laravel applications to interact with the Mistral.ai API, providing
straightforward access to features like chat completions and text embeddings.

Get your API key at [console.mistral.ai](https://console.mistral.ai/).

## Installation

You can install the package via composer:

```bash
composer require helgesverre/mistral
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="mistral-config"
```

This is the contents of the published config file:

```php
return [
    'api_key' => env('MISTRAL_API_KEY'),
    'base_url' => env('MISTRAL_BASE_URL', 'https://api.mistral.ai'),
    'timeout' => env('MISTRAL_TIMEOUT', 30),
];
```

## Usage

### Client Instantiation

Create an instance of the Mistral client to start interacting with the API. This instance will be your primary interface
for sending requests to Mistral.AI.

```php
use HelgeSverre\Mistral\Enums\Model;
use HelgeSverre\Mistral\Mistral;

// Instantiate the client
$mistral = new Mistral(apiKey: config('mistral.api_key'));

// Or use the Facade (Laravel)
Mistral::chat();
Mistral::simpleChat();
Mistral::embedding();
Mistral::models();
```

## Available Resources & Methods

The Mistral PHP client provides 14 resource classes, each offering both Response-returning methods and typed DTO methods for convenient type-safe usage.

### Chat Resource

Access via `$mistral->chat()`

**Methods:**

- `create(...)`: Response - Create a chat completion
- `createDto(...)`: ChatCompletionResponse - Create a chat completion and return typed DTO
- `createStreamed(...)`: Generator - Stream chat completions

**Example:**

```php
$completion = $mistral->chat()->createDto(
    messages: [['role' => 'user', 'content' => 'Hello!']],
    model: Model::small->value
);
```

### SimpleChat Resource

Access via `$mistral->simpleChat()`

**Methods:**

- `create(...)`: SimpleChatResponse - Simplified chat completion (returns flattened DTO directly)
- `stream(...)`: Generator - Stream simplified chat completions

**Example:**

```php
$response = $mistral->simpleChat()->create(
    messages: [['role' => 'user', 'content' => 'Hello!']],
    model: Model::medium->value
);
echo $response->content; // Direct access to content
```

### Embedding Resource

Access via `$mistral->embedding()`

**Methods:**

- `create(array $input, ...)`: Response - Create embeddings
- `createDto(array $input, ...)`: EmbeddingResponse - Create embeddings and return typed DTO

**Example:**

```php
$embeddings = $mistral->embedding()->createDto([
    'Text to embed',
    'Another text'
]);
```

### Models Resource

Access via `$mistral->models()`

**Methods:**

- `list()`: Response - List available models
- `listDto()`: ModelList - List models and return typed DTO
- `retrieve(string $modelId)`: BaseModelCard|FTModelCard - Get model details
- `delete(string $modelId)`: DeleteModelOut - Delete a fine-tuned model

**Example:**

```php
$models = $mistral->models()->listDto();
foreach ($models->data as $model) {
    echo $model->id;
}
```

### OCR Resource

Access via `$mistral->ocr()`

**Methods:**

- `process(...)`: Response - Process document with OCR
- `processDto(...)`: OCRResponse - Process and return typed DTO
- `processUrl(string $url, ...)`: Response - Process document from URL
- `processUrlDto(string $url, ...)`: OCRResponse - Process URL and return typed DTO
- `processBase64(string $base64, ...)`: Response - Process base64 encoded document
- `processBase64Dto(string $base64, ...)`: OCRResponse - Process base64 and return typed DTO

**Example:**

```php
$result = $mistral->ocr()->processUrlDto(
    url: 'https://example.com/document.pdf'
);
```

### FIM Resource (Fill-in-the-Middle)

Access via `$mistral->fim()`

**Methods:**

- `create(...)`: Response - Create FIM completion
- `createDto(...)`: FIMCompletionResponse - Create and return typed DTO
- `createStreamed(...)`: Generator - Stream FIM completions

**Example:**

```php
$completion = $mistral->fim()->createDto(
    model: 'codestral-latest',
    prompt: 'def fibonacci(',
    suffix: '    return result'
);
```

### Agents Resource

Access via `$mistral->agents()`

**Methods:**

- `create(AgentCreationRequest $request)`: Response - Create an agent
- `createDto(AgentCreationRequest $request)`: Agent - Create and return typed DTO
- `list(?int $page, ?int $pageSize)`: Response - List agents
- `listDto(?int $page, ?int $pageSize)`: AgentList - List and return typed DTO
- `get(string $agentId)`: Response - Get agent details
- `getDto(string $agentId)`: Agent - Get agent and return typed DTO
- `update(string $agentId, AgentUpdateRequest $request)`: Response - Update agent
- `updateDto(string $agentId, AgentUpdateRequest $request)`: Agent - Update and return typed DTO
- `updateVersion(string $agentId, int $version)`: Response - Switch agent version
- `updateVersionDto(string $agentId, int $version)`: Agent - Switch version and return typed DTO

### Conversations Resource

Access via `$mistral->conversations()`

**Methods:**

- `create(ConversationRequest $request)`: Response - Create conversation
- `createDto(ConversationRequest $request)`: ConversationResponse - Create and return typed DTO
- `createStreamed(ConversationRequest $request)`: Generator - Create with streaming
- `list(?int $page, ?int $pageSize, ?string $order)`: Response - List conversations
- `listDto(...)`: ConversationList - List and return typed DTO
- `get(string $conversationId)`: Response - Get conversation
- `getDto(string $conversationId)`: ConversationResponse - Get and return typed DTO
- `append(string $conversationId, ConversationAppendRequest $request)`: Response - Append to conversation
- `appendDto(...)`: ConversationResponse - Append and return typed DTO
- `appendStreamed(...)`: Generator - Append with streaming
- `getHistory(string $conversationId)`: Response - Get conversation history
- `getHistoryDto(string $conversationId)`: ConversationHistory - Get history and return typed DTO
- `getMessages(string $conversationId)`: Response - Get conversation messages
- `getMessagesDto(string $conversationId)`: ConversationMessages - Get messages and return typed DTO
- `restart(string $conversationId, ConversationRestartRequest $request)`: Response - Restart conversation
- `restartDto(...)`: ConversationResponse - Restart and return typed DTO
- `restartStreamed(...)`: Generator - Restart with streaming

### Audio Resource

Access via `$mistral->audio()`

**Methods:**

- `transcribe(string $filePath, ...)`: Response - Transcribe audio
- `transcribeDto(string $filePath, ...)`: TranscriptionResponse - Transcribe and return typed DTO
- `transcribeStreamed(string $filePath, ...)`: Generator - Transcribe with streaming

**Example:**

```php
$transcription = $mistral->audio()->transcribeDto(
    filePath: '/path/to/audio.mp3',
    model: 'whisper-large-v3'
);
```

### Files Resource

Access via `$mistral->files()`

**Methods:**

- `upload(string $filePath, ?FilePurpose $purpose)`: Response - Upload file
- `uploadDto(...)`: UploadFileOut - Upload and return typed DTO
- `list(...)`: Response - List files with filters
- `listDto(...)`: ListFilesOut - List and return typed DTO
- `retrieve(string $fileId)`: Response - Get file metadata
- `retrieveDto(string $fileId)`: RetrieveFileOut - Get metadata and return typed DTO
- `delete(string $fileId)`: Response - Delete file
- `deleteDto(string $fileId)`: DeleteFileOut - Delete and return typed DTO
- `download(string $fileId)`: Response - Download file content
- `getSignedUrl(string $fileId, ?int $expiry)`: Response - Get signed download URL
- `getSignedUrlDto(string $fileId, ?int $expiry)`: FileSignedURL - Get URL and return typed DTO

### FineTuning Resource

Access via `$mistral->fineTuning()`

**Methods:**

- `list(...)`: Response - List fine-tuning jobs
- `listAsDto(...)`: JobsOut - List and return typed DTO
- `create(JobIn $jobIn, ?bool $dryRun)`: Response - Create fine-tuning job
- `createAsDto(JobIn $jobIn, ?bool $dryRun)`: CompletionJobOut|ClassifierJobOut|LegacyJobMetadataOut
- `get(string $jobId)`: Response - Get job details
- `getAsDto(string $jobId)`: CompletionDetailedJobOut|ClassifierDetailedJobOut
- `cancel(string $jobId)`: Response - Cancel job
- `cancelAsDto(string $jobId)`: CompletionDetailedJobOut|ClassifierDetailedJobOut
- `start(string $jobId)`: Response - Start validated job
- `startAsDto(string $jobId)`: CompletionDetailedJobOut|ClassifierDetailedJobOut
- `updateModel(string $modelId, UpdateFTModelIn $update)`: Response - Update model metadata
- `updateModelAsDto(...)`: CompletionFTModelOut|ClassifierFTModelOut
- `archiveModel(string $modelId)`: Response - Archive model
- `archiveModelAsDto(string $modelId)`: ArchiveFTModelOut
- `unarchiveModel(string $modelId)`: Response - Unarchive model
- `unarchiveModelAsDto(string $modelId)`: UnarchiveFTModelOut

### Batch Resource

Access via `$mistral->batch()`

**Methods:**

- `list(...)`: Response - List batch jobs
- `listAsDto(...)`: BatchJobsOut - List and return typed DTO
- `create(BatchJobIn $batchJobIn)`: Response - Create batch job
- `createAsDto(BatchJobIn $batchJobIn)`: BatchJobOut - Create and return typed DTO
- `get(string $jobId)`: Response - Get batch job details
- `getAsDto(string $jobId)`: BatchJobOut - Get and return typed DTO
- `cancel(string $jobId)`: Response - Cancel batch job
- `cancelAsDto(string $jobId)`: BatchJobOut - Cancel and return typed DTO

### Classifications Resource

Access via `$mistral->classifications()`

**Methods:**

- `moderate(string $model, string|array $input)`: Response - Moderate content
- `moderateAsDto(string $model, string|array $input)`: ModerationResponse - Moderate and return typed DTO
- `moderateChat(string $model, array $input)`: Response - Moderate chat messages
- `moderateChatAsDto(string $model, array $input)`: ModerationResponse - Moderate chat and return typed DTO
- `classify(string $model, string|array $input)`: Response - Classify content
- `classifyAsDto(string $model, string|array $input)`: ClassificationResponse - Classify and return typed DTO
- `classifyChat(string $model, array $messages)`: Response - Classify chat
- `classifyChatAsDto(string $model, array $messages)`: ClassificationResponse - Classify chat and return typed DTO

**Example:**

```php
$moderation = $mistral->classifications()->moderateAsDto(
    model: 'mistral-moderation-latest',
    input: 'Text to moderate'
);
```

### Libraries Resource

Access via `$mistral->libraries()`

**Methods:**

- `list(?int $page, ?int $pageSize)`: Response - List libraries
- `create(LibraryIn $library)`: Response - Create library
- `get(string $libraryId)`: Response - Get library details
- `update(string $libraryId, LibraryInUpdate $library)`: Response - Update library
- `delete(string $libraryId)`: Response - Delete library
- `listDocuments(string $libraryId, ...)`: Response - List documents in library
- `uploadDocument(string $libraryId, string $filePath)`: Response - Upload document
- `getDocument(string $libraryId, string $documentId)`: Response - Get document details
- `updateDocument(string $libraryId, string $documentId, DocumentUpdateIn $update)`: Response - Update document
- `deleteDocument(string $libraryId, string $documentId)`: Response - Delete document
- `listSharing(string $libraryId)`: Response - List library sharing settings
- `createSharing(string $libraryId, SharingIn $sharing)`: Response - Create/update sharing
- `deleteSharing(string $libraryId, SharingDelete $sharing)`: Response - Delete sharing

## Resources

### `Models` Resource

#### List available models

```php
// Get Response object
$response = $mistral->models()->list();

// Or get typed DTO directly
/** @var \HelgeSverre\Mistral\Dto\Models\ModelList $models */
$models = $mistral->models()->listDto();
```

### `Embeddings` Resource

#### Create embedding

```php
// Get Response object
$response = $mistral->embedding()->create([
    "A string here",
    "Another one here",
]);

// Or get typed DTO directly
/** @var EmbeddingResponse $embeddings */
$embeddings = $mistral->embedding()->createDto([
    "A string here",
    "Another one here",
]);
```

### `Chat` Resource

#### Create Chat Completion

```php
// Get Response object
$response = $mistral->chat()->create(
    messages: [
        [
            "role" => "user",
            "content" => "Write hello world in BASH",
        ]
    ],
    model: Model::medium->value,
    temperature: 0.4,
    maxTokens: 100,
    safeMode: false
);

// Or get typed DTO directly
/** @var ChatCompletionResponse $completion */
$completion = $mistral->chat()->createDto(
    messages: [
        [
            "role" => "user",
            "content" => "Write hello world in BASH",
        ]
    ],
    model: Model::medium->value,
    temperature: 0.4,
    maxTokens: 100,
    safeMode: false
);
```

#### Create Chat Completion with Function Calling

```php
$response = $this->mistral->chat()->create(
    messages: [
        [
            'role' => Role::user->value,
            'content' => 'What is the weather in Bergen, Norway?',
        ],
    ],
    model: Model::large->value,
    maxTokens: 1000,
    tools: [
        [
            'type' => 'function',
            'function' => [
                'name' => 'searchWeather',
                'description' => 'Get the weather for a location',
                'parameters' => [
                    'type' => 'object',
                    'required' => [
                        'location',
                    ],
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'The location to get the weather for.',
                        ],
                    ],
                ],
            ],
        ],
        [
            'type' => 'function',
            'function' => [
                'name' => 'sendWeatherNotification',
                'description' => 'Send notification about weather to a user',
                'parameters' => [
                    'type' => 'object',
                    'required' => [
                        'userId',
                        'message',
                    ],
                    'properties' => [
                        'userId' => [
                            'type' => 'string',
                            'description' => 'the id of the user',
                        ],
                        'message' => [
                            'type' => 'string',
                            'description' => 'the message to send the user',
                        ],
                    ],
                ],
            ],
        ],
    ],
    toolChoice: 'any',
);

// Tool calls are returned in the response
$response->json('choices.0.message.tool_calls');
$response->json('choices.0.message.tool_calls.0.id');
$response->json('choices.0.message.tool_calls.0.type');
$response->json('choices.0.message.tool_calls.0.function');
$response->json('choices.0.message.tool_calls.0.function.name');
$response->json('choices.0.message.tool_calls.0.function.arguments');


// Or using the dto

/** @var ChatCompletionResponse $dto */
$dto = $response->dto();

$dto->choices; // array of ChatCompletionChoice

foreach ($dto->choices as $choice) {

    $choice->message; // ChatCompletionMessage

    foreach ($choice->message->toolCalls as $toolCall) {
        $toolCall->id; // null
        $toolCall->type; // function
        $toolCall->function; // FunctionCall
        $toolCall->function->name; // 'searchWeather'
        $toolCall->function->arguments; // '{"location":"Bergen, Norway"}'
        $toolCall->function->args(); // ['location' => 'Bergen, Norway']
    }
}
```

#### Create Streamed Chat Completions

```php
// Returns a generator, which you can iterate over to get the streamed chunks
$stream = $this->mistral->chat()->createStreamed(
    messages: [
        [
            'role' => 'user',
            'content' => 'Make a markdown list of 10 common fruits'
        ],
    ],
    model: Model::small->value,
);

foreach ($stream as $chunk) {

    /** @var StreamedChatCompletionResponse $chunk */

    echo $chunk->id; // 'cmpl-0339459d35cb441b9f111b94216cff97'
    echo $chunk->model; // 'mistral-small'
    echo $chunk->object; // 'chat.completion.chunk'
    echo $chunk->created; // DateTime

    foreach ($chunk->choices as $choice) {
        $choice->index; // 0
        $choice->delta->role; // 'assistant'
        $choice->delta->content; // 'Fruit list...'
        $choice->finishReason; // 'length'
    }
}
```

### `SimpleChat` Resource

For convenience, the client also provides a simple chat completion method, which returns a simpler, condensed and
flattened DTO, which is useful for quick prototyping.

#### Create simple chat completions

```php
$response = $mistral->simpleChat()->create(
    messages: [
        [
            "role" => "user",
            "content" => "Hello world!",
        ],
    ],
    model: Model::medium->value,
    temperature: 0.4,
    maxTokens: 1500,
    safeMode: false
);

/** @var ChatCompletionResponse $response */
```

### `SimpleChat` Resource

For convenience, the client also provides a simple chat completion method, which returns a simpler, condensed, and
flattened DTO, useful for quick prototyping.

#### Create Streamed Simple Chat Completions

```php
// Returns a generator, which you can iterate over to get the streamed chunks
$response = $this->mistral->simpleChat()->stream(
    messages: [
        [
            'role' => "user",
            'content' => 'Say the word "banana"',
        ],
    ],
    maxTokens: 100,
);

foreach ($response as $chunk) {
    /** @var SimpleStreamChunk $chunk */

    $chunk->id;           // 'cmpl-716e95d336db4e51a04cbcf2b84d1a76'
    $chunk->model;        // 'mistral-medium'
    $chunk->object;       // 'chat.completion.chunk'
    $chunk->created;      // '2024-01-03 12:00:00'
    $chunk->role;         // 'assistant'
    $chunk->content;      // 'the text \n'
    $chunk->finishReason; // 'length'
}
```

## List of DTOs

For convenience, here is a list of all the DTOs available in this package, organized by feature area.

### Chat

- [Chat/ChatCompletionChoice.php](./src/Dto/Chat/ChatCompletionChoice.php)
- [Chat/ChatCompletionMessage.php](./src/Dto/Chat/ChatCompletionMessage.php)
- [Chat/ChatCompletionRequest.php](./src/Dto/Chat/ChatCompletionRequest.php)
- [Chat/ChatCompletionResponse.php](./src/Dto/Chat/ChatCompletionResponse.php)
- [Chat/StreamedChatCompletionChoice.php](./src/Dto/Chat/StreamedChatCompletionChoice.php)
- [Chat/StreamedChatCompletionDelta.php](./src/Dto/Chat/StreamedChatCompletionDelta.php)
- [Chat/StreamedChatCompletionResponse.php](./src/Dto/Chat/StreamedChatCompletionResponse.php)
- [Chat/FunctionCall.php](./src/Dto/Chat/FunctionCall.php)
- [Chat/ToolCalls.php](./src/Dto/Chat/ToolCalls.php)

### SimpleChat

- [SimpleChat/SimpleChatResponse.php](./src/Dto/SimpleChat/SimpleChatResponse.php)
- [SimpleChat/SimpleStreamChunk.php](./src/Dto/SimpleChat/SimpleStreamChunk.php)

### Embeddings

- [Embedding/EmbeddingRequest.php](./src/Dto/Embedding/EmbeddingRequest.php)
- [Embedding/EmbeddingResponse.php](./src/Dto/Embedding/EmbeddingResponse.php)

### Fill-in-the-Middle (FIM)

- [Fim/FIMCompletionRequest.php](./src/Dto/Fim/FIMCompletionRequest.php)
- [Fim/FIMCompletionResponse.php](./src/Dto/Fim/FIMCompletionResponse.php)
- [Fim/FIMChoice.php](./src/Dto/Fim/FIMChoice.php)
- [Fim/StreamedFIMCompletionResponse.php](./src/Dto/Fim/StreamedFIMCompletionResponse.php)
- [Fim/StreamedFIMChoice.php](./src/Dto/Fim/StreamedFIMChoice.php)
- [Fim/StreamedFIMDelta.php](./src/Dto/Fim/StreamedFIMDelta.php)

### OCR

- [OCR/OCRRequest.php](./src/Dto/OCR/OCRRequest.php)
- [OCR/OCRResponse.php](./src/Dto/OCR/OCRResponse.php)
- [OCR/Document.php](./src/Dto/OCR/Document.php)
- [OCR/Page.php](./src/Dto/OCR/Page.php)
- [OCR/Image.php](./src/Dto/OCR/Image.php)
- [OCR/Dimensions.php](./src/Dto/OCR/Dimensions.php)
- [OCR/UsageInfo.php](./src/Dto/OCR/UsageInfo.php)

### Models

- [Models/Model.php](./src/Dto/Models/Model.php)
- [Models/ModelList.php](./src/Dto/Models/ModelList.php)
- [Models/ModelPermission.php](./src/Dto/Models/ModelPermission.php)
- [Models/ModelCapabilities.php](./src/Dto/Models/ModelCapabilities.php)
- [Models/BaseModelCard.php](./src/Dto/Models/BaseModelCard.php)
- [Models/FTModelCard.php](./src/Dto/Models/FTModelCard.php)
- [Models/DeleteModelOut.php](./src/Dto/Models/DeleteModelOut.php)

### Fine-Tuning

- [FineTuning/JobIn.php](./src/Dto/FineTuning/JobIn.php)
- [FineTuning/JobsOut.php](./src/Dto/FineTuning/JobsOut.php)
- [FineTuning/JobMetadata.php](./src/Dto/FineTuning/JobMetadata.php)
- [FineTuning/LegacyJobMetadataOut.php](./src/Dto/FineTuning/LegacyJobMetadataOut.php)
- [FineTuning/CompletionJobOut.php](./src/Dto/FineTuning/CompletionJobOut.php)
- [FineTuning/ClassifierJobOut.php](./src/Dto/FineTuning/ClassifierJobOut.php)
- [FineTuning/CompletionDetailedJobOut.php](./src/Dto/FineTuning/CompletionDetailedJobOut.php)
- [FineTuning/ClassifierDetailedJobOut.php](./src/Dto/FineTuning/ClassifierDetailedJobOut.php)
- [FineTuning/CompletionFTModelOut.php](./src/Dto/FineTuning/CompletionFTModelOut.php)
- [FineTuning/ClassifierFTModelOut.php](./src/Dto/FineTuning/ClassifierFTModelOut.php)
- [FineTuning/ArchiveFTModelOut.php](./src/Dto/FineTuning/ArchiveFTModelOut.php)
- [FineTuning/UnarchiveFTModelOut.php](./src/Dto/FineTuning/UnarchiveFTModelOut.php)
- [FineTuning/UpdateFTModelIn.php](./src/Dto/FineTuning/UpdateFTModelIn.php)
- [FineTuning/TrainingFile.php](./src/Dto/FineTuning/TrainingFile.php)
- [FineTuning/TrainingParameters.php](./src/Dto/FineTuning/TrainingParameters.php)
- [FineTuning/WandbIntegration.php](./src/Dto/FineTuning/WandbIntegration.php)
- [FineTuning/Checkpoint.php](./src/Dto/FineTuning/Checkpoint.php)
- [FineTuning/CheckpointMetrics.php](./src/Dto/FineTuning/CheckpointMetrics.php)
- [FineTuning/ValidationError.php](./src/Dto/FineTuning/ValidationError.php)

### Files

- [Files/FileObject.php](./src/Dto/Files/FileObject.php)
- [Files/UploadFileOut.php](./src/Dto/Files/UploadFileOut.php)
- [Files/ListFilesOut.php](./src/Dto/Files/ListFilesOut.php)
- [Files/ListFilesRequest.php](./src/Dto/Files/ListFilesRequest.php)
- [Files/RetrieveFileOut.php](./src/Dto/Files/RetrieveFileOut.php)
- [Files/DeleteFileOut.php](./src/Dto/Files/DeleteFileOut.php)
- [Files/FileSignedURL.php](./src/Dto/Files/FileSignedURL.php)

### Batch

- [Batch/BatchJobIn.php](./src/Dto/Batch/BatchJobIn.php)
- [Batch/BatchJobOut.php](./src/Dto/Batch/BatchJobOut.php)
- [Batch/BatchJobsOut.php](./src/Dto/Batch/BatchJobsOut.php)

### Audio

- [Audio/TranscriptionResponse.php](./src/Dto/Audio/TranscriptionResponse.php)
- [Audio/TranscriptionSegment.php](./src/Dto/Audio/TranscriptionSegment.php)
- [Audio/TranscriptionWord.php](./src/Dto/Audio/TranscriptionWord.php)

### Classifications & Moderation

- [Classifications/ClassificationRequest.php](./src/Dto/Classifications/ClassificationRequest.php)
- [Classifications/ChatClassificationRequest.php](./src/Dto/Classifications/ChatClassificationRequest.php)
- [Classifications/ClassificationResponse.php](./src/Dto/Classifications/ClassificationResponse.php)
- [Classifications/ClassificationResult.php](./src/Dto/Classifications/ClassificationResult.php)
- [Moderations/ChatModerationRequest.php](./src/Dto/Moderations/ChatModerationRequest.php)
- [Moderations/ModerationResponse.php](./src/Dto/Moderations/ModerationResponse.php)
- [Moderations/ModerationResult.php](./src/Dto/Moderations/ModerationResult.php)
- [Moderations/ModerationCategories.php](./src/Dto/Moderations/ModerationCategories.php)
- [Moderations/ModerationCategoryScores.php](./src/Dto/Moderations/ModerationCategoryScores.php)

### Agents

- [Agents/Agent.php](./src/Dto/Agents/Agent.php)
- [Agents/AgentList.php](./src/Dto/Agents/AgentList.php)
- [Agents/AgentCreationRequest.php](./src/Dto/Agents/AgentCreationRequest.php)
- [Agents/AgentUpdateRequest.php](./src/Dto/Agents/AgentUpdateRequest.php)

### Conversations

- [Conversations/ConversationRequest.php](./src/Dto/Conversations/ConversationRequest.php)
- [Conversations/ConversationAppendRequest.php](./src/Dto/Conversations/ConversationAppendRequest.php)
- [Conversations/ConversationRestartRequest.php](./src/Dto/Conversations/ConversationRestartRequest.php)
- [Conversations/ConversationResponse.php](./src/Dto/Conversations/ConversationResponse.php)
- [Conversations/ConversationList.php](./src/Dto/Conversations/ConversationList.php)
- [Conversations/ConversationHistory.php](./src/Dto/Conversations/ConversationHistory.php)
- [Conversations/ConversationMessages.php](./src/Dto/Conversations/ConversationMessages.php)
- [Conversations/ConversationEntry.php](./src/Dto/Conversations/ConversationEntry.php)
- [Conversations/ConversationMessage.php](./src/Dto/Conversations/ConversationMessage.php)
- [Conversations/ModelConversation.php](./src/Dto/Conversations/ModelConversation.php)
- [Conversations/AgentConversation.php](./src/Dto/Conversations/AgentConversation.php)

### Libraries

- [Libraries/LibraryIn.php](./src/Dto/Libraries/LibraryIn.php)
- [Libraries/LibraryInUpdate.php](./src/Dto/Libraries/LibraryInUpdate.php)
- [Libraries/LibraryOut.php](./src/Dto/Libraries/LibraryOut.php)
- [Libraries/ListLibraryOut.php](./src/Dto/Libraries/ListLibraryOut.php)
- [Libraries/DocumentOut.php](./src/Dto/Libraries/DocumentOut.php)
- [Libraries/DocumentUpdateIn.php](./src/Dto/Libraries/DocumentUpdateIn.php)
- [Libraries/ListDocumentOut.php](./src/Dto/Libraries/ListDocumentOut.php)
- [Libraries/SharingIn.php](./src/Dto/Libraries/SharingIn.php)
- [Libraries/SharingDelete.php](./src/Dto/Libraries/SharingDelete.php)
- [Libraries/SharingOut.php](./src/Dto/Libraries/SharingOut.php)
- [Libraries/ListSharingOut.php](./src/Dto/Libraries/ListSharingOut.php)

### Shared/Common

- [Usage.php](./src/Dto/Usage.php)

## List of available Mistral models

The following models are available in the Mistral API. You can use the `Model` enum in this package to refer to them, or use the string value directly.

### Current Production Models

| Enum Case                    | String Value              | Type       | Description                                                             |
| ---------------------------- | ------------------------- | ---------- | ----------------------------------------------------------------------- |
| `Model::large->value`        | `'mistral-large-latest'`  | Chat       | Most capable model for complex reasoning and specialized tasks          |
| `Model::medium->value`       | `'mistral-medium-latest'` | Chat       | Balanced model for intermediate tasks (alias: mistral-medium-2508)      |
| `Model::small->value`        | `'mistral-small-latest'`  | Chat       | Fast, efficient model for simple bulk tasks (alias: mistral-small-2506) |
| `Model::pixtralLarge->value` | `'pixtral-large-latest'`  | Vision     | Advanced vision model for image understanding                           |
| `Model::pixtral12b->value`   | `'pixtral-12b-latest'`    | Vision     | Efficient vision model                                                  |
| `Model::codestral->value`    | `'codestral-latest'`      | Code       | Specialized model for code generation and understanding                 |
| `Model::ministral8b->value`  | `'ministral-8b-latest'`   | Chat       | Compact 8B parameter model                                              |
| `Model::ministral3b->value`  | `'ministral-3b-latest'`   | Chat       | Ultra-compact 3B parameter model for edge deployment                    |
| `Model::embed->value`        | `'mistral-embed'`         | Embeddings | Text embedding model for semantic search and retrieval                  |

### Open Source Models

| Enum Case                 | String Value          | Type | Description                                   |
| ------------------------- | --------------------- | ---- | --------------------------------------------- |
| `Model::mistral7b->value` | `'open-mistral-7b'`   | Chat | 7B parameter open source foundation model     |
| `Model::mixtral->value`   | `'open-mixtral-8x7b'` | Chat | Mixture of Experts model with 8x7B parameters |

For the most up-to-date model information and capabilities, visit the [Mistral AI Models Documentation](https://docs.mistral.ai/models/).

## Testing

```bash
cp .env.example .env
composer test
composer analyse src
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Disclaimer

Mistral and the Mistral logo are trademarks of Mistral.ai. This package is not affiliated with, endorsed by, or
sponsored by Mistral.ai. All trademarks and registered trademarks are the property of their respective owners.

See [Mistral.AI](https://mistral.ai/terms-of-use/) for more information.
