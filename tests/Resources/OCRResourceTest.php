<?php

/** @noinspection PhpUnhandledExceptionInspection */

use HelgeSverre\Mistral\Dto\OCR\Document;
use HelgeSverre\Mistral\Requests\OCR\ProcessDocument;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

beforeEach(function () {
    $this->mistral = new HelgeSverre\Mistral\Mistral(apiKey: config('mistral.api_key'));
});

it('ProcessDocument works with URL', function () {
    Saloon::fake([
        ProcessDocument::class => MockResponse::fixture('ocr.processDocument'),
    ]);

    $response = $this->mistral->ocr()->process(
        model: 'mistral-ocr-latest',
        document: 'https://pdfa.org/download-area/cheat-sheets/Color.pdf',
        includeImageBase64: true,
    );

    Saloon::assertSent(ProcessDocument::class);

    // The fixture shows a 400 error because the example URL cannot be fetched
    expect($response->status())->toBe(400)
        ->and($response->json())->toBeArray()
        ->and($response->json('message'))->toBe("File could not be fetched from url 'https://example.com/document.pdf'");
});

it('ProcessDocument works with processUrl method', function () {
    Saloon::fake([
        ProcessDocument::class => MockResponse::fixture('ocr.processDocument'),
    ]);

    $response = $this->mistral->ocr()->processUrl(
        url: 'https://pdfa.org/download-area/cheat-sheets/Color.pdf',
        model: 'mistral-ocr-latest',
        includeImageBase64: true,
    );

    Saloon::assertSent(ProcessDocument::class);

    expect($response->status())->toBe(400);
});

it('ProcessDocument works with base64', function () {
    Saloon::fake([
        ProcessDocument::class => MockResponse::fixture('ocr.processDocument'),
    ]);

    $base64Data = base64_encode('PDF content here');

    $response = $this->mistral->ocr()->process(
        model: 'mistral-ocr-latest',
        document: $base64Data,
        mimeType: 'application/pdf',
        includeImageBase64: false,
    );

    Saloon::assertSent(ProcessDocument::class);

    expect($response->status())->toBe(400);
});

it('ProcessDocument works with processBase64 method', function () {
    Saloon::fake([
        ProcessDocument::class => MockResponse::fixture('ocr.processDocument'),
    ]);

    $base64Data = base64_encode('PDF content here');

    $response = $this->mistral->ocr()->processBase64(
        base64: $base64Data,
        mimeType: 'application/pdf',
        model: 'mistral-ocr-latest',
        includeImageBase64: false,
    );

    Saloon::assertSent(ProcessDocument::class);

    expect($response->status())->toBe(400);
});

it('ProcessDocument works with Document object', function () {
    Saloon::fake([
        ProcessDocument::class => MockResponse::fixture('ocr.processDocument'),
    ]);

    $document = Document::fromUrl('https://pdfa.org/download-area/cheat-sheets/Color.pdf');

    $response = $this->mistral->ocr()->process(
        model: 'mistral-ocr-latest',
        document: $document,
        includeImageBase64: true,
    );

    Saloon::assertSent(ProcessDocument::class);

    expect($response->status())->toBe(400);
});

it('ProcessDocument response shows error for invalid URL', function () {
    Saloon::fake([
        ProcessDocument::class => MockResponse::fixture('ocr.processDocument'),
    ]);

    $response = $this->mistral->ocr()->processUrl(
        url: 'https://pdfa.org/download-area/cheat-sheets/Color.pdf',
        includeImageBase64: true,
    );

    Saloon::assertSent(ProcessDocument::class);

    expect($response->status())->toBe(400)
        ->and($response->json('object'))->toBe('error')
        ->and($response->json('type'))->toBe('invalid_request_file')
        ->and($response->json('code'))->toBe('3310');
});

it('ProcessDocument throws exception when base64 is provided without mimeType', function () {
    $base64Data = base64_encode('PDF content here');

    expect(fn () => $this->mistral->ocr()->process(
        model: 'mistral-ocr-latest',
        document: $base64Data,
    ))->toThrow(InvalidArgumentException::class, 'MIME type is required when passing base64 encoded data');
});

it('Document::fromBase64 creates correct data URL for document', function () {
    $base64 = 'SGVsbG8gV29ybGQ=';
    $mimeType = 'application/pdf';

    $document = Document::fromBase64($base64, $mimeType);

    expect($document->type)->toBe('document_url')
        ->and($document->documentUrl)->toBe('data:application/pdf;base64,SGVsbG8gV29ybGQ=');
});

it('Document::fromBase64 creates correct data URL for image', function () {
    $base64 = 'SGVsbG8gV29ybGQ=';
    $mimeType = 'image/png';

    $document = Document::fromBase64($base64, $mimeType);

    expect($document->type)->toBe('image_url')
        ->and($document->imageUrl)->toBe('data:image/png;base64,SGVsbG8gV29ybGQ=');
});

it('Document::fromUrl creates correct document object', function () {
    $url = 'https://example.com/document.pdf';

    $document = Document::fromUrl($url);

    expect($document->type)->toBe('document_url')
        ->and($document->documentUrl)->toBe($url);
});

it('Document::fromDocumentUrl creates correct document object', function () {
    $url = 'https://example.com/document.pdf';

    $document = Document::fromDocumentUrl($url);

    expect($document->type)->toBe('document_url')
        ->and($document->documentUrl)->toBe($url);
});

it('Document::fromImageUrl creates correct image object', function () {
    $url = 'https://example.com/image.png';

    $document = Document::fromImageUrl($url);

    expect($document->type)->toBe('image_url')
        ->and($document->imageUrl)->toBe($url);
});

it('Document::fromFileId creates correct document object', function () {
    $fileId = 'file-abc123';

    $document = Document::fromFileId($fileId);

    expect($document->type)->toBe('file')
        ->and($document->fileId)->toBe($fileId);
});
