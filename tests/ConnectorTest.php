<?php

/** @noinspection PhpUnhandledExceptionInspection */
it('There is a default timeout', function () {
    $mistral = new HelgeSverre\Mistral\Mistral(
        apiKey: config('mistral.api_key'),
    );

    expect($mistral->getRequestTimeout())->toEqual(env('MISTRAL_TIMEOUT', 90));
});

it('Can change the timeout', function () {
    $mistral = new HelgeSverre\Mistral\Mistral(
        apiKey: config('mistral.api_key'),
        timeout: 10
    );

    expect($mistral->getRequestTimeout())->toEqual(10);
});

it('Can change the timeout to 0', function () {
    $mistral = new HelgeSverre\Mistral\Mistral(
        apiKey: config('mistral.api_key'),
        timeout: 0
    );

    expect($mistral->getRequestTimeout())->toEqual(0);
});
