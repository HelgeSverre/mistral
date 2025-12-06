# Mistral PHP SDK - Development Roadmap

Current state: **179 tests passing**, PHPStan level 5 clean.

## Legend
- **S** = Small (<1h)
- **M** = Medium (1-3h)  
- **L** = Large (1-2 days)

---

## Completed ✅

### A1. Agents Completion Endpoint (M) ✅
Implemented `/v1/agents/completions` endpoint:
- [x] Created `Dto\Agents\AgentsCompletionRequest` DTO
- [x] Created `Requests\Agents\CreateAgentsCompletionRequest`
- [x] Added `complete()`, `completeDto()`, and `completeStreamed()` methods to `Resource\Agents`
- [x] Added tests and fixtures

### A2. Libraries Document Extras (M) ✅
Implemented missing endpoints under `beta.libraries.documents`:
- [x] `GET /libraries/{id}/documents/{docId}/text_content` → `GetDocumentTextContent`
- [x] `GET /libraries/{id}/documents/{docId}/status` → `GetDocumentStatus`
- [x] `GET /libraries/{id}/documents/{docId}/signed-url` → `GetDocumentSignedUrl`
- [x] `GET /libraries/{id}/documents/{docId}/extracted-text-signed-url` → `GetExtractedTextSignedUrl`
- [x] `POST /libraries/{id}/documents/{docId}/reprocess` → `ReprocessDocument`
- [x] Created `Dto\Libraries\DocumentTextContent` and `ProcessingStatusOut` DTOs
- [x] Added wrapper methods to `Resource\Libraries`

### B. Missing Models in Enum (S) ✅
- [x] Added `case ministral3b = 'ministral-3b-latest'`
- [x] Added `case mistralNemo = 'open-mistral-nemo'`
- [x] Updated `withJsonModeSupport()` array

### C1. Fine-tuning `JobIn` DTO (M) ✅
- [x] Changed `validationFiles` type to `string[]` (UUIDs)
- [x] Changed `integrations` to `?array` (WandbIntegration[])
- [x] Added missing fields: `invalidSampleSkipPercentage`, `jobType`, `repositories`, `classifierTargets`
- [x] Created `GithubRepositoryIn` and `ClassifierTargetIn` DTOs

### C3. Batch `BatchJobIn` DTO (S) ✅
- [x] Removed deprecated `completionWindow` field
- [x] Added documentation for `metadata` constraint

### C4. Files `ListFilesRequest` DTO (S) ✅
- [x] Changed `sampleType` to `sampleTypes` (array)
- [x] Changed `source` to `sources` (array)
- [x] Updated `toArray()` to output arrays of enum values
- [x] Updated `Resource\Files` to use new parameter names

### C7. Agents DTOs (S) ✅
- [x] Added `handoffs` field to `Agent`, `AgentCreationRequest`, and `AgentUpdateRequest` DTOs

---

## Remaining Work

### D. Code Quality & Maintenance

#### D1. Dependency Updates (M)

Major version upgrades available:
- [ ] `larastan/larastan` 2.x → 3.x
- [ ] `pestphp/pest` 2.x → 4.x
- [ ] `pestphp/pest-plugin-*` 2.x → 4.x
- [ ] `phpstan/phpstan-*` 1.x → 2.x
- [ ] `orchestra/testbench` 9.x → 10.x

#### D2. Test Improvements (S)
- [ ] Review `old/` directory scripts for any useful fixture generation patterns
- [ ] Delete `old/` directory contents once confirmed unnecessary

#### D3. Documentation (S)
- [ ] Update README.md with new endpoints (agents/completions, libraries document extras)
- [ ] Update model table with new models

---

## E. Optional Enhancements

### E1. Streaming Paths Alignment (S-M)
- [ ] Verify streaming works correctly against live API
- [ ] Document streaming behavior

### E2. Retry Logic (M)
- [ ] Consider adding retry logic for 429/5xx errors
- [ ] Use Saloon's retry middleware

### E3. Typed Stream DTOs (L)
- [ ] Add typed stream DTOs for Conversations
- [ ] Add DTO-returning methods for Libraries resource

### C5. Conversations List Response (M)
- [ ] Verify actual API response format against live API
- [ ] Update if spec is correct (returns plain array, not pagination wrapper)

### C6. Chat `ChatCompletionRequest` DTO (S-M)
- [ ] Consider typed `ResponseFormat` DTO instead of `?array`
- [ ] Consider typed `ToolChoice` DTO/enum instead of `?string`
- [ ] Add reasoning mode property if applicable

---

## Implementation Summary

| Task | Status | Tests Added |
|------|--------|-------------|
| Agents Completion | ✅ | +2 tests |
| Libraries Document Extras | ✅ | 0 (API methods ready) |
| Missing Models | ✅ | 0 |
| Fine-tuning JobIn | ✅ | 0 (existing tests pass) |
| Batch BatchJobIn | ✅ | Updated test |
| Files ListFilesRequest | ✅ | Updated test |
| Agents handoffs | ✅ | 0 |

**Test count: 177 → 179**

---

*Last updated: 2025-12-06*
