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
use HelgeSverre\Mistral\Requests\Files\DeleteFile;
use HelgeSverre\Mistral\Requests\Files\DownloadFile;
use HelgeSverre\Mistral\Requests\Files\GetSignedUrl;
use HelgeSverre\Mistral\Requests\Files\ListFiles;
use HelgeSverre\Mistral\Requests\Files\RetrieveFile;
use HelgeSverre\Mistral\Requests\Files\UploadFile;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
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
        ->and($dto->data->id)->toBe('file-550e8400-e29b-41d4-a716-446655440000')
        ->and($dto->data->object)->toBe('file')
        ->and($dto->data->bytes)->toBe(1024)
        ->and($dto->data->filename)->toBe('training_data.jsonl')
        ->and($dto->data->purpose)->toBe(FilePurpose::FINE_TUNE)
        ->and($dto->data->sampleType)->toBe(SampleType::INSTRUCT)
        ->and($dto->data->numLines)->toBe(100)
        ->and($dto->data->source)->toBe(Source::UPLOAD);

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
        ->and($dto->total)->toBe(2)
        ->and($dto->data)->toHaveCount(2)
        ->and($dto->data[0]->id)->toBe('file-550e8400-e29b-41d4-a716-446655440000')
        ->and($dto->data[0]->filename)->toBe('training_data.jsonl')
        ->and($dto->data[0]->purpose)->toBe(FilePurpose::FINE_TUNE)
        ->and($dto->data[1]->id)->toBe('file-660e8400-e29b-41d4-a716-446655440001')
        ->and($dto->data[1]->filename)->toBe('batch_data.jsonl')
        ->and($dto->data[1]->purpose)->toBe(FilePurpose::BATCH);
});

it('can list files with filters', function () {
    Saloon::fake([
        ListFiles::class => MockResponse::fixture('files/list'),
    ]);

    $response = $this->mistral->files()->list(
        page: 0,
        pageSize: 10,
        sampleType: SampleType::INSTRUCT,
        source: Source::UPLOAD,
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

    $fileId = 'file-550e8400-e29b-41d4-a716-446655440000';
    $response = $this->mistral->files()->retrieve($fileId);

    Saloon::assertSent(RetrieveFile::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(RetrieveFileOut::class)
        ->and($dto->id)->toBe($fileId)
        ->and($dto->object)->toBe('file')
        ->and($dto->bytes)->toBe(1024)
        ->and($dto->filename)->toBe('training_data.jsonl')
        ->and($dto->purpose)->toBe(FilePurpose::FINE_TUNE)
        ->and($dto->sampleType)->toBe(SampleType::INSTRUCT)
        ->and($dto->numLines)->toBe(100)
        ->and($dto->source)->toBe(Source::UPLOAD);
});

it('can delete a file', function () {
    Saloon::fake([
        DeleteFile::class => MockResponse::fixture('files/delete'),
    ]);

    $fileId = 'file-550e8400-e29b-41d4-a716-446655440000';
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

    $fileId = 'file-550e8400-e29b-41d4-a716-446655440000';
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

    $fileId = 'file-550e8400-e29b-41d4-a716-446655440000';
    $response = $this->mistral->files()->getSignedUrl($fileId);

    Saloon::assertSent(GetSignedUrl::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(FileSignedURL::class)
        ->and($dto->url)->toStartWith('https://storage.googleapis.com/mistral-files/');
});

it('can get signed URL with custom expiry', function () {
    Saloon::fake([
        GetSignedUrl::class => MockResponse::fixture('files/signedUrl'),
    ]);

    $fileId = 'file-550e8400-e29b-41d4-a716-446655440000';
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
