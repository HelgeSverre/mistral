<?php

namespace HelgeSverre\Mistral;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MistralServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('mistral')->hasConfigFile();
    }

    public function packageBooted(): void
    {
        $this->app->bind(Mistral::class, function () {
            return new Mistral(
                apiKey: config('mistral.api_key'),
                baseUrl: config('mistral.base_url'),
                timeout: config('mistral.timeout', 30),
            );
        });
    }
}
