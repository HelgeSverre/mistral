<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Enums\Model;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
});

it('ListModels works', function () {

    $chunks = $this->mistral->chat()->createStreamed(
        messages: [
            ['role' => 'user', 'content' => 'Make a markdown list of 10 common fruits'],
        ],
        model: Model::tiny->value,
    );

    foreach ($chunks as $chunk) {

        dump($chunk->choices[0]->delta?->content);

    }

});
