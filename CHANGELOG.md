# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - 2025-10-11

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
