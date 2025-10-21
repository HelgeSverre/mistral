# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-10-21

### Added

- **Typed DTO Wrapper Methods**: All Resource classes now include typed `*Dto()` wrapper methods (e.g., `createDto()`, `listDto()`) that return properly typed DTOs instead of generic Response objects, providing perfect IDE autocomplete without needing inline `@var` annotations
- **Updated Model Support**: Added 6 new model enum cases based on current Mistral AI API:
  - `ministral8b` (edge model)
  - `magistralMedium` (reasoning model)
  - `codestral` (coding model)
  - `pixtralLarge` (vision model)
  - `pixtral12b` (vision model)
  - `voxtralSmall` (audio model)
- **Files Resource DTO Methods**: Added `uploadDto()`, `listDto()`, `retrieveDto()`, `deleteDto()`, `getSignedUrlDto()`
- **Audio Resource DTO Method**: Added `transcribeDto()`
- **Models Resource DTO Method**: Added `listDto()`

### Changed

- **Comprehensive README Update**: Expanded documentation from 348 to 744 lines
  - Documented all 98 DTOs (previously only 16 were listed)
  - Added complete "Available Resources & Methods" section covering all 14 Resource classes
  - Updated model list with current Mistral AI models
  - All usage examples now demonstrate typed DTO methods
- **Example Updates**: All 10 example files and READMEs updated to use new typed DTO methods
- **Cleaned Configuration**: Removed unused environment variables from `.env.example` files (reduced from 47 to 14 lines)
- **Model Enum Organization**: Reorganized with clear category comments (general purpose, edge, reasoning, coding, vision, audio, embedding, deprecated)
- **Updated `withJsonModeSupport()`**: Now includes new models that support JSON mode

### Documentation

- Updated all code samples to use `*Dto()` methods instead of `->dto()` calls
- Removed unnecessary `@var` annotations from examples (no longer needed with typed methods)
- 346 total line changes across example README files
- Complete API reference documentation for all resources

### Developer Experience

- Perfect IDE autocomplete with native PHP return types
- Zero runtime overhead (thin wrapper methods)
- No breaking changes - original methods unchanged
- Type-safe without complex PHPDoc annotations

## [1.0.0] - 2025-10-11

### Added

- 40+ new API endpoints covering complete Mistral AI API
- Chat Completions with streaming support
- Models API (list, retrieve, delete)
- OCR API for document processing (images and PDFs)
- FIM (Fill-in-Middle) completions for code generation
- Audio transcriptions with streaming support
- Files management (upload, list, retrieve, delete, download, signed URLs)
- Fine-tuning API - complete workflow:
    - Create fine-tuning jobs (with dry-run support)
    - List fine-tuning jobs with filtering
    - Get detailed job information
    - Cancel running jobs
    - Start pending jobs
    - Update fine-tuned models
    - Archive and unarchive models
- Batch processing API for asynchronous requests
- Moderations API (text and chat moderation)
- Classifications API (text and chat classification)
- Agents API (Beta) - agent creation and management
- Conversations API (Beta) - multi-turn conversations with streaming
- Libraries API (Beta) - RAG document management:
    - 18 endpoints covering full document lifecycle
    - Collections management
    - Documents upload, list, retrieve, update, delete
    - Advanced RAG operations
- Comprehensive test coverage with 80+ test fixtures
- PHPStan level 5 static analysis
- Type-safe DTOs using Spatie Laravel Data
- Automatic snake_case to camelCase mapping

### Changed

- Reorganized examples/ directory with tutorial-style guides
- Updated documentation to reflect 100% Mistral AI API coverage
- Enhanced error handling and response validation

### Fixed

- **Standalone Examples Bootstrap**: Fixed Laravel Data compatibility for standalone PHP examples
    - Added minimal Laravel container configuration in `examples/shared/bootstrap.php`
    - Configured required Laravel Data settings without full Laravel application
    - Disabled validation strategy to avoid facade dependencies
    - Examples now run independently without requiring Laravel installation

### Removed

- `generate-ocr-fixture.php` (development script no longer needed)

## [1.3.1] - 2024

### Fixed

- Minor bug fixes and improvements

## [1.3.0] - 2024

### Added

- Laravel 11 support

## [1.2.1] - 2024

### Fixed

- Bug fixes and stability improvements

## [1.2.0] - 2024

### Changed

- Updated documentation
- Code formatting improvements

## [1.1.0] - 2024

### Added

- Function calling support
- Regenerated test fixtures

### Changed

- Code formatting improvements
