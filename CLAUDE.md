# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Build/Test/Lint

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Run static analysis (PHPStan level 5)
composer analyse src

# Format code with Laravel Pint
composer format
```

### Testing a specific test file

```bash
composer test -- tests/Resources/YourResourceTest.php
```

### Requirements

- PHP 8.2+
- Laravel 8.x, 9.x, or 11.x compatibility (tested via Orchestra Testbench)

## High-level Architecture

This is a Laravel package for the Mistral.ai API built on top of Saloon PHP HTTP client.

### Key Patterns

1. **Resource Pattern**: Each API endpoint group has a Resource class (e.g., `Chat`, `OCR`, `Embedding`) that extends `Saloon\Http\BaseResource` and provides high-level methods.

2. **Request/Response DTOs**:
    - Request DTOs in `src/Dto/[Feature]/` extend `Spatie\LaravelData\Data`
    - Use `#[MapName()]` attributes for snake_case to camelCase mapping
    - Response DTOs match API response structure exactly

3. **Request Classes**: In `src/Requests/[Feature]/` extend `Saloon\Http\Request` with:
    - `HasBody` interface and `HasJsonBody` trait
    - Constructor accepts DTO request object
    - `defaultBody()` returns `array_filter($dto->toArray())` to remove null values
    - `createDtoFromResponse()` converts responses to DTOs

4. **Main Connector**: `src/Mistral.php` extends `Saloon\Http\Connector` and provides:
    - Fluent access to resources via methods like `->chat()`, `->ocr()`, etc.
    - Token authentication via `TokenAuthenticator`
    - Configurable timeout and base URL

### Adding New Endpoints

1. Create DTOs in `src/Dto/[Feature]/`
2. Create Request class in `src/Requests/[Feature]/`
3. Create Resource class in `src/Resource/`
4. Add resource method to `Mistral.php`
5. Create tests in `tests/Resources/`
6. Add fixture in `tests/Fixtures/Saloon/`

### Testing Pattern

Tests use Pest PHP with the following patterns:

1. **Mocking**: Use `Saloon::fake()` to mock HTTP requests
2. **Fixtures**: JSON response fixtures in `tests/Fixtures/Saloon/` follow naming: `{feature}.{method}[-optional-descriptor].json`
3. **Assertions**: Use `Saloon::assertSent()` to verify requests were made
4. **DTOs**: Call `$response->dto()` to test DTO conversion

### Streaming Responses

Resources that support streaming (Chat, SimpleChat, FIM, Audio, Conversations):

- Include `use HandlesStreamedResponses` trait
- Return `Generator` that yields DTO instances
- Use `getStreamIterator($response->stream())` to parse SSE format

## Important Notes

- All API parameters use camelCase in PHP but snake_case in JSON (handled by `#[MapName()]` attribute)
- Request DTOs use nullable properties with defaults for maximum flexibility
- Response DTOs use Spatie Laravel Data collections (`DataCollection`) for arrays
- The `array_filter()` in Request `defaultBody()` removes null values before sending to API
- PHPStan is configured at level 5 - maintain this strict typing standard
