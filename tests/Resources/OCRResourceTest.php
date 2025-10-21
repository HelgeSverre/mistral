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
        ProcessDocument::class => MockResponse::fixture('ocr.processDocumentSuccess'),
    ]);

    $response = $this->mistral->ocr()->process(
        model: 'mistral-ocr-latest',
        document: 'https://pdfa.org/download-area/cheat-sheets/Color.pdf',
        includeImageBase64: false,
    );

    Saloon::assertSent(ProcessDocument::class);

    expect($response->status())->toBe(200);

    $dto = $response->dto();
    expect($dto)->toBeInstanceOf(\HelgeSverre\Mistral\Dto\OCR\OCRResponse::class)
        ->and($dto->model)->toBe('mistral-ocr-2505-completion')
        ->and($dto->pages)->toHaveCount(3)
        ->and($dto->pages[0]->index)->toBe(0)
        ->and($dto->pages[0]->markdown)->toContain('PDF Association Cheat Sheet')
        ->and($dto->pages[0]->images)->toHaveCount(6)
        ->and($dto->usageInfo->pagesProcessed)->toBe(3)
        ->and($dto->usageInfo->docSizeBytes)->toBe(953033);
});

it('ProcessDocument throws exception for error responses', function () {
    Saloon::fake([
        ProcessDocument::class => MockResponse::fixture('ocr.processDocument'),
    ]);

    expect(fn () => $this->mistral->ocr()->processUrl(
        url: 'https://pdfa.org/download-area/cheat-sheets/Color.pdf',
        model: 'mistral-ocr-latest',
        includeImageBase64: true,
    ))->toThrow(\Saloon\Exceptions\Request\ClientException::class);

    Saloon::assertSent(ProcessDocument::class);
});

it('ProcessDocument with base64 throws exception on error', function () {
    Saloon::fake([
        ProcessDocument::class => MockResponse::fixture('ocr.processDocument'),
    ]);

    $base64Data = base64_encode('PDF content here');

    expect(fn () => $this->mistral->ocr()->process(
        model: 'mistral-ocr-latest',
        document: $base64Data,
        mimeType: 'application/pdf',
        includeImageBase64: false,
    ))->toThrow(\Saloon\Exceptions\Request\ClientException::class);

    Saloon::assertSent(ProcessDocument::class);
});

it('ProcessDocument with processBase64 throws exception on error', function () {
    Saloon::fake([
        ProcessDocument::class => MockResponse::fixture('ocr.processDocument'),
    ]);

    $base64Data = base64_encode('PDF content here');

    expect(fn () => $this->mistral->ocr()->processBase64(
        base64: $base64Data,
        mimeType: 'application/pdf',
        model: 'mistral-ocr-latest',
        includeImageBase64: false,
    ))->toThrow(\Saloon\Exceptions\Request\ClientException::class);

    Saloon::assertSent(ProcessDocument::class);
});

it('ProcessDocument with Document object throws exception on error', function () {
    Saloon::fake([
        ProcessDocument::class => MockResponse::fixture('ocr.processDocument'),
    ]);

    $document = Document::fromUrl('https://pdfa.org/download-area/cheat-sheets/Color.pdf');

    expect(fn () => $this->mistral->ocr()->process(
        model: 'mistral-ocr-latest',
        document: $document,
        includeImageBase64: true,
    ))->toThrow(\Saloon\Exceptions\Request\ClientException::class);

    Saloon::assertSent(ProcessDocument::class);
});

it('ProcessDocument throws exception with error details for invalid URL', function () {
    Saloon::fake([
        ProcessDocument::class => MockResponse::fixture('ocr.processDocument'),
    ]);

    try {
        $this->mistral->ocr()->processUrl(
            url: 'https://pdfa.org/download-area/cheat-sheets/Color.pdf',
            includeImageBase64: true,
        );

        expect(true)->toBe(false, 'Exception should have been thrown');
    } catch (\Saloon\Exceptions\Request\ClientException $e) {
        expect($e->getResponse()->status())->toBe(400)
            ->and($e->getResponse()->json('object'))->toBe('error')
            ->and($e->getResponse()->json('type'))->toBe('invalid_request_file')
            ->and($e->getResponse()->json('code'))->toBe('3310');
    }

    Saloon::assertSent(ProcessDocument::class);
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

it('ProcessDocument throws exception and includes error details', function () {
    Saloon::fake([
        ProcessDocument::class => MockResponse::fixture('ocr.processDocument'),
    ]);

    try {
        $this->mistral->ocr()->processUrl(
            url: 'https://example.com/document.pdf',
            includeImageBase64: false,
        );

        expect(true)->toBe(false, 'Exception should have been thrown');
    } catch (\Saloon\Exceptions\Request\ClientException $e) {
        expect($e->getResponse()->status())->toBe(400)
            ->and($e->getResponse()->json('object'))->toBe('error')
            ->and($e->getResponse()->json('type'))->toBe('invalid_request_file')
            ->and($e->getResponse()->json('code'))->toBe('3310')
            ->and($e->getResponse()->json('message'))->toContain('File could not be fetched from url');
    }

    Saloon::assertSent(ProcessDocument::class);
});
