<?php

namespace HelgeSverre\Mistral\Tests;

use Dotenv\Dotenv;
use HelgeSverre\Mistral\MistralServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Saloon\Laravel\SaloonServiceProvider;
use Spatie\LaravelData\LaravelDataServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            SaloonServiceProvider::class,
            MistralServiceProvider::class,
            LaravelDataServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // Load .env.test into the environment.
        if (file_exists(dirname(__DIR__).'/.env')) {
            (Dotenv::createImmutable(dirname(__DIR__), '.env'))->load();
        }

        config()->set('mistral.api_key', env('MISTRAL_API_KEY'));
    }
}
