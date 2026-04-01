<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\Files\DeleteFileOut;
use HelgeSverre\Mistral\Dto\Files\FileSignedURL;
use HelgeSverre\Mistral\Dto\Files\ListFilesOut;
use HelgeSverre\Mistral\Dto\Files\RetrieveFileOut;
use HelgeSverre\Mistral\Dto\Files\UploadFileOut;
use HelgeSverre\Mistral\Enums\FilePurpose;
use HelgeSverre\Mistral\Enums\SampleType;
use HelgeSverre\Mistral\Enums\Source;
use HelgeSverre\Mistral\Mistral;
use HelgeSverre\Mistral\Requests\Files\DeleteFile;
use HelgeSverre\Mistral\Requests\Files\DownloadFile;
use HelgeSverre\Mistral\Requests\Files\GetSignedUrl;
use HelgeSverre\Mistral\Requests\Files\ListFiles;
use HelgeSverre\Mistral\Requests\Files\RetrieveFile;
use HelgeSverre\Mistral\Requests\Files\UploadFile;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

beforeEach(function () {
    $this->mistral = new Mistral(apiKey: config('mistral.api_key'));
});

it('can upload a file', function () {
    Saloon::fake([
        UploadFile::class => MockResponse::fixture('files/upload'),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'test_');
    file_put_contents($tempFile, '{"prompt": "test", "completion": "test"}');

    $response = $this->mistral->files()->upload(
        filePath: $tempFile,
        purpose: FilePurpose::FINE_TUNE
    );

    Saloon::assertSent(UploadFile::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(UploadFileOut::class)
        ->and($dto->id)->toBe('ed3e56f8-8df4-4548-a5bc-a4e07ff29d09')
        ->and($dto->object)->toBe('file')
        ->and($dto->bytes)->toBe(409)
        ->and($dto->filename)->toBe('mistral_test_D2Ip0s')
        ->and($dto->purpose)->toBe(FilePurpose::FINE_TUNE)
        ->and($dto->sampleType)->toBe(SampleType::INSTRUCT)
        ->and($dto->numLines)->toBe(2)
        ->and($dto->source)->toBe(Source::UPLOAD);

    unlink($tempFile);
});

it('can upload a file without purpose', function () {
    Saloon::fake([
        UploadFile::class => MockResponse::fixture('files/upload'),
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'test_');
    file_put_contents($tempFile, '{"prompt": "test", "completion": "test"}');

    $response = $this->mistral->files()->upload(
        filePath: $tempFile
    );

    Saloon::assertSent(UploadFile::class);
    expect($response->status())->toBe(200);

    unlink($tempFile);
});

it('can list files', function () {
    Saloon::fake([
        ListFiles::class => MockResponse::fixture('files/list'),
    ]);

    $response = $this->mistral->files()->list();

    Saloon::assertSent(ListFiles::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(ListFilesOut::class)
        ->and($dto->object)->toBe('list')
        ->and($dto->total)->toBe(3)
        ->and($dto->data)->toHaveCount(3)
        ->and($dto->data[0]->id)->toBe('0a980fee-f172-4555-b2ca-3b70e43b036c')
        ->and($dto->data[0]->filename)->toBe('batch-input.jsonl')
        ->and($dto->data[0]->purpose)->toBe(FilePurpose::BATCH)
        ->and($dto->data[1]->id)->toBe('92eceef6-84a2-4bc4-a064-ab6b6ec5b9a2')
        ->and($dto->data[1]->filename)->toBe('batch-input.jsonl')
        ->and($dto->data[1]->purpose)->toBe(FilePurpose::BATCH)
        ->and($dto->data[2]->id)->toBe('615c701a-38f0-4101-9765-649919412a7c')
        ->and($dto->data[2]->filename)->toBe('training-data.jsonl')
        ->and($dto->data[2]->purpose)->toBe(FilePurpose::FINE_TUNE);
});

it('can list files with filters', function () {
    Saloon::fake([
        ListFiles::class => MockResponse::fixture('files/list'),
    ]);

    $response = $this->mistral->files()->list(
        page: 0,
        pageSize: 10,
        sampleTypes: [SampleType::INSTRUCT],
        sources: [Source::UPLOAD],
        search: 'training',
        purpose: FilePurpose::FINE_TUNE
    );

    Saloon::assertSent(ListFiles::class);
    expect($response->status())->toBe(200);
});

it('can retrieve a file', function () {
    Saloon::fake([
        RetrieveFile::class => MockResponse::fixture('files/retrieve'),
    ]);

    $fileId = '615c701a-38f0-4101-9765-649919412a7c';
    $response = $this->mistral->files()->retrieve($fileId);

    Saloon::assertSent(RetrieveFile::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(RetrieveFileOut::class)
        ->and($dto->id)->toBe($fileId)
        ->and($dto->object)->toBe('file')
        ->and($dto->bytes)->toBe(865)
        ->and($dto->filename)->toBe('training-data.jsonl')
        ->and($dto->purpose)->toBe(FilePurpose::FINE_TUNE)
        ->and($dto->sampleType)->toBe(SampleType::INSTRUCT)
        ->and($dto->numLines)->toBe(5)
        ->and($dto->source)->toBe(Source::UPLOAD);
});

it('can delete a file', function () {
    Saloon::fake([
        DeleteFile::class => MockResponse::fixture('files/delete'),
    ]);

    $fileId = 'ed3e56f8-8df4-4548-a5bc-a4e07ff29d09';
    $response = $this->mistral->files()->delete($fileId);

    Saloon::assertSent(DeleteFile::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(DeleteFileOut::class)
        ->and($dto->id)->toBe($fileId)
        ->and($dto->object)->toBe('file')
        ->and($dto->deleted)->toBeTrue();
});

it('can download a file', function () {
    Saloon::fake([
        DownloadFile::class => MockResponse::make(
            body: 'file content here',
            status: 200,
            headers: ['Content-Type' => 'application/octet-stream']
        ),
    ]);

    $fileId = '615c701a-38f0-4101-9765-649919412a7c';
    $response = $this->mistral->files()->download($fileId);

    Saloon::assertSent(DownloadFile::class);

    expect($response->status())->toBe(200)
        ->and($response->body())->toBe('file content here')
        ->and($response->header('Content-Type'))->toBe('application/octet-stream');
});

it('can get signed URL', function () {
    Saloon::fake([
        GetSignedUrl::class => MockResponse::fixture('files/signedUrl'),
    ]);

    $fileId = '615c701a-38f0-4101-9765-649919412a7c';
    $response = $this->mistral->files()->getSignedUrl($fileId);

    Saloon::assertSent(GetSignedUrl::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(FileSignedURL::class)
        ->and($dto->url)->toContain('blob.core.windows.net');
});

it('can get signed URL with custom expiry', function () {
    Saloon::fake([
        GetSignedUrl::class => MockResponse::fixture('files/signedUrl'),
    ]);

    $fileId = '615c701a-38f0-4101-9765-649919412a7c';
    $response = $this->mistral->files()->getSignedUrl($fileId, expiry: 48);

    Saloon::assertSent(GetSignedUrl::class);

    expect($response->status())->toBe(200);
});

it('FilePurpose enum has correct values', function () {
    expect(FilePurpose::FINE_TUNE->value)->toBe('fine-tune')
        ->and(FilePurpose::BATCH->value)->toBe('batch');
});

it('SampleType enum has correct values', function () {
    expect(SampleType::PRETRAIN->value)->toBe('pretrain')
        ->and(SampleType::INSTRUCT->value)->toBe('instruct');
});

it('Source enum has correct values', function () {
    expect(Source::UPLOAD->value)->toBe('upload')
        ->and(Source::REPOSITORY->value)->toBe('repository');
});
