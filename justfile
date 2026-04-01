# Mistral PHP Justfile
# Run `just` or `just --list` to see available commands

# Default recipe: show available commands
[private]
default:
    @just --list --unsorted

# ─────────────────────────────────────────────────────────────────────────────
# Testing
# ─────────────────────────────────────────────────────────────────────────────

# Run all tests
[group('test')]
test:
    composer test

# Run tests with coverage report
[group('test')]
coverage *args:
    #!/usr/bin/env bash
    if command -v herd &> /dev/null; then
        herd coverage vendor/bin/pest --coverage --coverage-html=coverage {{ args }}
    else
        composer test-coverage
    fi
    open coverage/index.html

# Run a specific test file or filter
[group('test')]
test-filter filter:
    composer test -- --filter="{{ filter }}"

# Run all examples (integration tests)
[group('test')]
examples:
    @echo "Running all examples..."
    @echo ""
    @echo "Example 1: Getting Started"
    @php examples/01-getting-started/getting-started.php
    @echo ""
    @echo "Example 2: Basic Chat"
    @php examples/02-basic-chat/basic-chat.php
    @echo ""
    @echo "Example 3: Chat Parameters"
    @php examples/03-chat-parameters/chat-parameters.php
    @echo ""
    @echo "Example 4: Streaming Chat"
    @php examples/04-streaming-chat/streaming-chat.php
    @echo ""
    @echo "Example 5: Function Calling"
    @php examples/05-function-calling/function-calling.php
    @echo ""
    @echo "Example 6: Embeddings"
    @php examples/06-embeddings/embeddings.php
    @echo ""
    @echo "Example 7: OCR"
    @php examples/07-ocr/ocr.php
    @echo ""
    @echo "Example 8: Audio"
    @php examples/08-audio/audio.php
    @echo ""
    @echo "Example 9: Moderation"
    @php examples/09-moderation/moderation.php
    @echo ""
    @echo "Example 10: Error Handling"
    @php examples/10-error-handling/error-handling.php
    @echo ""
    @echo "All examples completed successfully!"

# ─────────────────────────────────────────────────────────────────────────────
# Code Quality
# ─────────────────────────────────────────────────────────────────────────────

# Run static analysis with PHPStan
[group('quality')]
analyse:
    composer analyse src

# Run code formatter (Laravel Pint)
[group('quality')]
format:
    composer format

# Check code formatting without making changes
[group('quality')]
format-check:
    vendor/bin/pint --test

# Run static analysis (alias for analyse)
[group('quality')]
lint: analyse

# Run all quality checks (format, analyse, test)
[group('quality')]
check: format analyse test

# ─────────────────────────────────────────────────────────────────────────────
# Workflows
# ─────────────────────────────────────────────────────────────────────────────

# Simulate CI pipeline (format-check, analyse, test)
[group('workflow')]
ci: format-check analyse test

# Pre-PR checks (same as ci for this project)
[group('workflow')]
pr: format-check analyse test

# ─────────────────────────────────────────────────────────────────────────────
# Development
# ─────────────────────────────────────────────────────────────────────────────

# Install composer dependencies
[group('dev')]
install:
    composer install

# Update composer dependencies
[group('dev')]
update:
    composer update

# Clear all caches and generated files
[group('dev')]
clean:
    rm -rf .phpunit.cache
    rm -rf coverage
    rm -rf vendor

# Fresh setup (clean + install)
[group('dev')]
setup: clean install
