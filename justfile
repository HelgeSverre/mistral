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
