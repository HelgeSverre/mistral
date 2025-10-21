# Mistral PHP Justfile
# Run `just --list` to see all available commands

# Default recipe to display help
default:
    @just --list

# Run all tests
test:
    composer test

# Run tests with coverage
coverage:
    composer test-coverage

# Run static analysis with PHPStan
analyze:
    composer analyse src examples

# Format code with Laravel Pint
format:
    composer format

# Run static analysis (alias for analyze)
lint: analyze

# Run all quality checks (format, analyze, test)
check: format analyze test

# Install dependencies
install:
    composer install

# Update dependencies
update:
    composer update

# Run all examples (integration tests)
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
    @echo "✅ All examples completed successfully!"
