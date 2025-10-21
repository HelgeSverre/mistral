<?php

/**
 * Bootstrap file for all examples
 *
 * This file handles common setup tasks:
 * - Autoloading
 * - Environment variables
 * - Error handling
 * - Common configurations
 */

// Require composer autoloader from project root
$autoloadPath = __DIR__.'/../../vendor/autoload.php';

if (! file_exists($autoloadPath)) {
    exit("Autoloader not found. Please run 'composer install' from the project root.\n");
}

require_once $autoloadPath;

// Bootstrap minimal Laravel container for Laravel Data
// Laravel's helpers.php (loaded by composer) uses Container::getInstance()
// so we need to set up the global instance with config binding
$container = \Illuminate\Container\Container::getInstance();

if ($container === null || ! $container->bound('config')) {
    // Create new container if none exists
    if ($container === null) {
        $container = new \Illuminate\Container\Container;
        \Illuminate\Container\Container::setInstance($container);
    }

    // Register config repository with Laravel Data configuration
    // We provide a minimal config inline to avoid requiring Laravel Application
    $container->singleton('config', function () {
        return new \Illuminate\Config\Repository([
            'data' => [
                'date_format' => DATE_ATOM,
                'date_timezone' => null,
                'max_transformation_depth' => null,
                'throw_when_max_transformation_depth_reached' => true,
                'var_dumper_caster_mode' => 'development',
                'features' => [
                    'cast_and_transform_iterables' => false,
                ],
                'wrap' => null,
                'transformers' => [
                    \DateTimeInterface::class => \Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer::class,
                    \Illuminate\Contracts\Support\Arrayable::class => \Spatie\LaravelData\Transformers\ArrayableTransformer::class,
                    \BackedEnum::class => \Spatie\LaravelData\Transformers\EnumTransformer::class,
                ],
                'casts' => [
                    \DateTimeInterface::class => \Spatie\LaravelData\Casts\DateTimeInterfaceCast::class,
                    \BackedEnum::class => \Spatie\LaravelData\Casts\EnumCast::class,
                ],
                'rule_inferrers' => [
                    \Spatie\LaravelData\Support\DataConfig\RuleInferrer\AttributesRuleInferrer::class,
                    \Spatie\LaravelData\Support\DataConfig\RuleInferrer\SometimesRuleInferrer::class,
                    \Spatie\LaravelData\Support\DataConfig\RuleInferrer\NullableRuleInferrer::class,
                    \Spatie\LaravelData\Support\DataConfig\RuleInferrer\RequiredRuleInferrer::class,
                    \Spatie\LaravelData\Support\DataConfig\RuleInferrer\BuiltInTypesRuleInferrer::class,
                ],
                'normalizers' => [
                    \Spatie\LaravelData\Normalizers\ModelNormalizer::class,
                    \Spatie\LaravelData\Normalizers\ArrayableNormalizer::class,
                    \Spatie\LaravelData\Normalizers\ObjectNormalizer::class,
                    \Spatie\LaravelData\Normalizers\ArrayNormalizer::class,
                    \Spatie\LaravelData\Normalizers\JsonNormalizer::class,
                ],
                'name_mapping_strategy' => [
                    'input' => null,
                    'output' => null,
                ],
                'ignore_invalid_partials' => false,
                'validation_strategy' => 'disabled', // Disable validation for standalone usage
                'structure_caching' => [
                    'enabled' => false, // Disable caching for standalone usage
                    'directories' => [],
                    'cache' => [
                        'store' => null,
                        'prefix' => 'laravel-data',
                        'duration' => null,
                    ],
                    'reflection_discovery' => [
                        'enabled' => false,
                        'directories' => [],
                    ],
                ],
                'commands' => [
                    'cache' => true,
                    'make' => true,
                ],
            ],
        ]);
    });
}

// Load environment variables from project root
$projectRoot = __DIR__.'/../..';
if (file_exists($projectRoot.'/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
    $dotenv->load();
}

// Validate required environment variables
if (! isset($_ENV['MISTRAL_API_KEY']) || $_ENV['MISTRAL_API_KEY'] === 'your_api_key_here' || $_ENV['MISTRAL_API_KEY'] === 'replace-me') {
    exit("\n❌ Error: MISTRAL_API_KEY not set or using default value.\n".
        "Please create a .env file in the project root and add your Mistral API key.\n".
        "You can get an API key at: https://console.mistral.ai/\n\n");
}

// Set up error handling
set_error_handler(function ($severity, $message, $file, $line) {
    if (! (error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Configure timezone
date_default_timezone_set('UTC');

// Load helper functions
if (file_exists(__DIR__.'/helpers.php')) {
    require_once __DIR__.'/helpers.php';
}

// Create a default Mistral client factory
function createMistralClient(): \HelgeSverre\Mistral\Mistral
{
    return new \HelgeSverre\Mistral\Mistral(
        apiKey: $_ENV['MISTRAL_API_KEY'],
        baseUrl: $_ENV['MISTRAL_BASE_URL'] ?? null,
        timeout: (int) ($_ENV['MISTRAL_TIMEOUT'] ?? 60)
    );
}
