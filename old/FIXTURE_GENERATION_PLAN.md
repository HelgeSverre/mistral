# Comprehensive Fixture Generation Plan

## Overview
This plan outlines the sequential creation of real resources to generate valid test fixtures for the Mistral PHP SDK test suite.

## Resource Dependency Analysis

### Independent Resources (No Dependencies)
These can be created first:

1. **Files** (`/v1/files`)
   - Upload a test file
   - Returns: `file_id`
   - Used by: Fine-tuning jobs, Batch jobs

2. **Models** (`/v1/models`)
   - Query available models
   - No creation needed (uses existing models)

### Dependent Resources (Require Prerequisites)

3. **Agents** (`/v1/agents`)
   - Create an agent
   - Returns: `agent_id`
   - Used by: Conversations (agent-based)

4. **Conversations** (`/v1/conversations`)
   - Can be model-based (no prerequisites) OR
   - Agent-based (requires agent_id)
   - Returns: `conversation_id`
   - Operations: Create, Append, Restart, Get, History, Messages

5. **Fine-tuning Jobs** (`/v1/fine_tuning/jobs`)
   - Requires: `file_id` (training data)
   - Returns: `job_id`, `model_id` (when complete)
   - Operations: Create, Start, Cancel, Get, Archive/Unarchive model

6. **Batch Jobs** (`/v1/batch/jobs`)
   - Requires: `file_id` (input file)
   - Returns: `batch_job_id`
   - Operations: Create, Get, Cancel

7. **Libraries** (`/v1/libraries`)
   - May require special authentication/permissions
   - Returns: `library_id`
   - Sub-resources: Documents, Sharing
   - Operations: Create, Update, Delete, Upload documents

8. **Audio** (`/v1/audio/transcriptions`)
   - Requires: Real audio file (MP3/WAV)
   - Model: `voxtral-mini-latest`

9. **OCR** (`/v1/ocr`)
   - Requires: Real document/image file
   - Model: `mistral-ocr-latest`

## Sequential Execution Plan

### Phase 1: Independent Resources (No API calls needed for fixtures)
These already have fixtures or don't need pre-existing resources:

- ✅ **Chat** - Works with any model
- ✅ **Embeddings** - Works standalone
- ✅ **Moderations** - Works standalone
- ✅ **Classifications** - Works standalone
- ✅ **FIM** - Works standalone
- ✅ **Simple Chat** - Works standalone

### Phase 2: File Upload
```php
// Create test files for fine-tuning and batch jobs
$trainingFile = uploadFile('training-data.jsonl', 'fine-tune');
$batchInputFile = uploadFile('batch-input.jsonl', 'batch');

// Store IDs for later use
$fileIds = [
    'training' => $trainingFile->id,
    'batch' => $batchInputFile->id,
];
```

### Phase 3: Agent Creation
```php
// Create test agent
$agent = createAgent([
    'name' => 'Test Agent',
    'model' => 'mistral-large-latest',
    'instructions' => 'You are a test agent',
]);

$agentId = $agent->id; // e.g., 'ag:123456:20241011:a1b2c3d4'
```

### Phase 4: Conversations
```php
// Model-based conversation
$conv1 = createConversation([
    'model' => 'mistral-large-latest',
    'messages' => [...],
]);

// Agent-based conversation (requires agent_id from Phase 3)
$conv2 = createConversation([
    'agent_id' => $agentId,
    'messages' => [...],
]);

$conversationIds = [
    'model_based' => $conv1->id,
    'agent_based' => $conv2->id,
];

// Test operations: append, restart, get, history, messages
```

### Phase 5: Fine-tuning Jobs
```php
// Create fine-tuning job (requires file_id from Phase 2)
$ftJob = createFineTuningJob([
    'model' => 'open-mistral-7b',
    'training_files' => [$fileIds['training']],
    'hyperparameters' => [...],
]);

$fineTuneJobId = $ftJob->id;

// Optional: Start, Cancel, Archive operations
```

### Phase 6: Batch Jobs
```php
// Create batch job (requires file_id from Phase 2)
$batchJob = createBatchJob([
    'input_files' => [$fileIds['batch']],
    'model' => 'mistral-large-latest',
    'endpoint' => '/v1/chat/completions',
]);

$batchJobId = $batchJob->id;

// Test operations: get, cancel
```

### Phase 7: Audio Transcription
```php
// Requires real audio file
$audioFile = '/path/to/test-audio.mp3'; // Need to provide this

$transcription = transcribeAudio([
    'file' => $audioFile,
    'model' => 'voxtral-mini-latest',
]);
```

### Phase 8: OCR
```php
// Requires real document/image
$documentUrl = 'https://example.com/test-document.pdf';

$ocr = processDocument([
    'document_url' => $documentUrl,
    'model' => 'mistral-ocr-latest',
]);
```

### Phase 9: Libraries (May require special auth)
```php
// Create library
$library = createLibrary([
    'name' => 'Test Library',
    'description' => 'Test library for fixtures',
]);

$libraryId = $library->id;

// Upload documents
$document = uploadDocument($libraryId, '/path/to/doc.pdf');
$documentId = $document->id;

// Test operations: update, delete, sharing
```

## Required Test Data Files

### 1. Fine-tuning Training Data (training-data.jsonl)
```jsonl
{"messages": [{"role": "user", "content": "Hello"}, {"role": "assistant", "content": "Hi there!"}]}
{"messages": [{"role": "user", "content": "How are you?"}, {"role": "assistant", "content": "I'm doing well!"}]}
```

### 2. Batch Input File (batch-input.jsonl)
```jsonl
{"custom_id": "request-1", "method": "POST", "url": "/v1/chat/completions", "body": {"model": "mistral-large-latest", "messages": [{"role": "user", "content": "What is AI?"}]}}
{"custom_id": "request-2", "method": "POST", "url": "/v1/chat/completions", "body": {"model": "mistral-large-latest", "messages": [{"role": "user", "content": "Explain machine learning"}]}}
```

### 3. Audio File (test-audio.mp3)
- Need a real MP3/WAV audio file with speech
- Can be short (10-30 seconds)
- Clear speech for testing

### 4. Document for OCR (test-document.pdf)
- Simple PDF with text
- Or image with text (PNG/JPG)

## Implementation Strategy

### Step 1: Create Resource Generator Script
Create `generate_fixtures.php` that:
1. Reads configuration for what resources to create
2. Creates resources in dependency order
3. Stores IDs in memory for dependent resources
4. Runs tests after each resource creation to generate fixtures
5. Outputs summary of created resources and their IDs

### Step 2: Create Test Data Files
- Generate JSONL files for fine-tuning and batch jobs
- Acquire/create audio file for transcription tests
- Acquire/create document for OCR tests

### Step 3: Execute Generator
```bash
php generate_fixtures.php
```

This will:
- Create all resources sequentially
- Trigger fixture generation via test runs
- Store resource IDs for cleanup

### Step 4: Update Tests
- Replace hardcoded IDs in tests with actual IDs from fixtures
- Update assertions to match real API response structure
- Handle nullable fields appropriately

### Step 5: Cleanup Script (Optional)
Create `cleanup_resources.php` to:
- Delete created agents
- Cancel/delete jobs
- Delete uploaded files
- Delete libraries/documents

## Fixture Validation

After generation, validate:
1. ✅ All fixtures have `"statusCode": 200`
2. ✅ Response structure matches DTO expectations
3. ✅ Required fields are present
4. ✅ IDs are consistent across related fixtures
5. ✅ Tests pass using fixtures (no real API calls)

## Expected Outcomes

### Before
- 75/177 tests passing (42%)
- 102 tests making real API calls
- Many missing fixtures

### After
- 160+/177 tests passing (90%+)
- Most tests using valid fixtures
- Only specialized tests skipped (libraries auth, etc.)

## Notes

1. **API Rate Limits**: The generator should implement delays between requests
2. **Cost**: Creating fine-tuning jobs may incur costs
3. **Cleanup**: Important to delete resources after fixture generation
4. **Authentication**: Libraries feature may require special API key permissions
5. **Idempotency**: Script should be re-runnable (check if resources exist first)

## Next Steps

1. Review and approve this plan
2. Create test data files
3. Implement generator script
4. Execute generation
5. Update tests
6. Verify all tests pass
