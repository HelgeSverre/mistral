<?php

it('can test', fn () => expect(true)->toBeTrue());

it('will not use debugging functions')->expect(['dd', 'dump', 'ray'])->each->not->toBeUsed();

it('All resource classes extend the base resource')
    ->expect('HelgeSverre\Mistral\Resource')
    ->toExtend(Saloon\Http\BaseResource::class);

it('All request classes extend the saloon request class')
    ->expect('HelgeSverre\Mistral\Requests')
    ->classes()
    ->toExtend(Saloon\Http\Request::class);
