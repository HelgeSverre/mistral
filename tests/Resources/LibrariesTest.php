<?php

use HelgeSverre\Mistral\Dto\Libraries\DocumentUpdateIn;
use HelgeSverre\Mistral\Dto\Libraries\LibraryIn;
use HelgeSverre\Mistral\Dto\Libraries\LibraryInUpdate;
use HelgeSverre\Mistral\Dto\Libraries\SharingDelete;
use HelgeSverre\Mistral\Dto\Libraries\SharingIn;
use HelgeSverre\Mistral\Enums\AccessRole;
use HelgeSverre\Mistral\Enums\EntityType;
use HelgeSverre\Mistral\Mistral;
use HelgeSverre\Mistral\Requests\Libraries\CreateLibrary;
use HelgeSverre\Mistral\Requests\Libraries\CreateSharing;
use HelgeSverre\Mistral\Requests\Libraries\DeleteDocument;
use HelgeSverre\Mistral\Requests\Libraries\DeleteLibrary;
use HelgeSverre\Mistral\Requests\Libraries\DeleteSharing;
use HelgeSverre\Mistral\Requests\Libraries\GetDocument;
use HelgeSverre\Mistral\Requests\Libraries\GetLibrary;
use HelgeSverre\Mistral\Requests\Libraries\ListDocuments;
use HelgeSverre\Mistral\Requests\Libraries\ListLibraries;
use HelgeSverre\Mistral\Requests\Libraries\ListSharing;
use HelgeSverre\Mistral\Requests\Libraries\UpdateDocument;
use HelgeSverre\Mistral\Requests\Libraries\UpdateLibrary;
use HelgeSverre\Mistral\Requests\Libraries\UploadDocument;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;
use Spatie\LaravelData\DataCollection;

beforeEach(function () {
    $this->mistral = new Mistral('test-api-key');
});

it('can list all libraries', function () {
    Saloon::fake([
        ListLibraries::class => MockResponse::fixture('libraries/list-libraries'),
    ]);

    $response = $this->mistral->libraries()->list();
    $dto = $response->dto();

    expect($dto)->toHaveProperty('data')
        ->and($dto)->toHaveProperty('total');

    // Data is a DataCollection, check if it's iterable
    expect($dto->data)->toBeIterable();

    // If there are libraries, verify structure
    if ($dto->data->count() > 0) {
        expect($dto->data[0])->toHaveProperty('id')
            ->and($dto->data[0])->toHaveProperty('name');
    }
});

it('can list libraries with pagination', function () {
    Saloon::fake([
        ListLibraries::class => MockResponse::fixture('libraries/list-libraries-paginated'),
    ]);

    $response = $this->mistral->libraries()->list(page: 1, pageSize: 10);
    $dto = $response->dto();

    expect($dto)->toHaveProperty('data')
        ->and($dto)->toHaveProperty('total')
        ->and($dto->data)->toBeInstanceOf(DataCollection::class)
        ->and($dto->total)->toBeInt();
});

it('can create a library', function () {
    Saloon::fake([
        CreateLibrary::class => MockResponse::fixture('libraries/create-library'),
    ]);

    $response = $this->mistral->libraries()->create(
        new LibraryIn(
            name: 'Product Documentation',
            description: 'All product docs for RAG'
        )
    );

    $dto = $response->dto();

    expect($dto)->toHaveProperty('id')
        ->and($dto->id)->toBeString()
        ->and($dto)->toHaveProperty('name')
        ->and($dto->name)->toBeString()
        ->and($dto)->toHaveProperty('createdAt')
        ->and($dto->createdAt)->toBeString()
        ->and($dto)->toHaveProperty('updatedAt')
        ->and($dto->updatedAt)->toBeString();
});

it('can get library details', function () {
    Saloon::fake([
        GetLibrary::class => MockResponse::fixture('libraries/get-library'),
    ]);

    $response = $this->mistral->libraries()->get('library-id-placeholder');
    $dto = $response->dto();

    expect($dto)->toHaveProperty('id')
        ->and($dto->id)->toBeString()
        ->and($dto)->toHaveProperty('name')
        ->and($dto->name)->toBeString();
});

it('can update a library', function () {
    Saloon::fake([
        UpdateLibrary::class => MockResponse::fixture('libraries/update-library'),
    ]);

    $response = $this->mistral->libraries()->update(
        libraryId: 'library-id-placeholder',
        library: new LibraryInUpdate(
            name: 'Updated Documentation',
            description: 'Updated description'
        )
    );

    $dto = $response->dto();

    expect($dto)->toHaveProperty('id')
        ->and($dto->id)->toBeString()
        ->and($dto)->toHaveProperty('name')
        ->and($dto->name)->toBeString();
});

it('can delete a library', function () {
    Saloon::fake([
        DeleteLibrary::class => MockResponse::make(status: 204),
    ]);

    $response = $this->mistral->libraries()->delete('550e8400-e29b-41d4-a716-446655440000');

    expect($response->status())->toBe(204);
});

it('can list documents in a library', function () {
    Saloon::fake([
        ListDocuments::class => MockResponse::fixture('libraries/list-documents'),
    ]);

    $response = $this->mistral->libraries()->listDocuments('library-id-placeholder');
    $dto = $response->dto();

    expect($dto)->toHaveProperty('data');
    expect($dto->data)->toBeIterable();

    if ($dto->data->count() > 0) {
        expect($dto->data[0])->toHaveProperty('name')
            ->and($dto->data[0])->toHaveProperty('processingStatus')
            ->and($dto->data[0])->toHaveProperty('libraryId');
    }
});

it('can list documents with search', function () {
    Saloon::fake([
        ListDocuments::class => MockResponse::fixture('libraries/list-documents-search'),
    ]);

    $response = $this->mistral->libraries()->listDocuments(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        search: 'API'
    );

    $dto = $response->dto();

    expect($dto)->toHaveProperty('data');
    // Verify structure regardless of actual count
    if ($dto->data->count() > 0) {
        expect($dto->data[0])->toHaveProperty('name');
    }
});

it('can list documents with pagination', function () {
    Saloon::fake([
        ListDocuments::class => MockResponse::fixture('libraries/list-documents-paginated'),
    ]);

    $response = $this->mistral->libraries()->listDocuments(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        page: 2,
        pageSize: 5
    );

    $dto = $response->dto();

    expect($dto)->toHaveProperty('data')
        ->and($dto)->toHaveProperty('total');

    // Total can be null or int in real API
    if ($dto->total !== null) {
        expect($dto->total)->toBeInt();
    }
});

it('can upload a document', function () {
    Saloon::fake([
        UploadDocument::class => MockResponse::fixture('libraries/upload-document'),
    ]);

    $response = $this->mistral->libraries()->uploadDocument(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        filePath: __DIR__.'/../Fixtures/test-document.txt'
    );

    $dto = $response->dto();

    expect($dto)->toHaveProperty('id')
        ->and($dto->id)->toBeString()
        ->and($dto)->toHaveProperty('libraryId')
        ->and($dto)->toHaveProperty('name')
        ->and($dto)->toHaveProperty('processingStatus');
});

it('can get document details', function () {
    Saloon::fake([
        GetDocument::class => MockResponse::fixture('libraries/get-document'),
    ]);

    $response = $this->mistral->libraries()->getDocument(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        documentId: '650e8400-e29b-41d4-a716-446655440001'
    );

    $dto = $response->dto();

    expect($dto)->toHaveProperty('id')
        ->and($dto->id)->toBeString()
        ->and($dto)->toHaveProperty('libraryId')
        ->and($dto)->toHaveProperty('name')
        ->and($dto)->toHaveProperty('processingStatus');
});

it('can update document metadata', function () {
    Saloon::fake([
        UpdateDocument::class => MockResponse::fixture('libraries/update-document'),
    ]);

    $response = $this->mistral->libraries()->updateDocument(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        documentId: '650e8400-e29b-41d4-a716-446655440001',
        update: new DocumentUpdateIn(name: 'renamed-document.pdf')
    );

    $dto = $response->dto();

    expect($dto)->toHaveProperty('id')
        ->and($dto->id)->toBeString()
        ->and($dto)->toHaveProperty('name');
});

it('can delete a document', function () {
    Saloon::fake([
        DeleteDocument::class => MockResponse::make(status: 204),
    ]);

    $response = $this->mistral->libraries()->deleteDocument(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        documentId: '650e8400-e29b-41d4-a716-446655440001'
    );

    expect($response->status())->toBe(204);
});

it('can list sharing access for a library', function () {
    Saloon::fake([
        ListSharing::class => MockResponse::fixture('libraries/list-sharing'),
    ]);

    $response = $this->mistral->libraries()->listSharing('550e8400-e29b-41d4-a716-446655440000');
    $dto = $response->dto();

    expect($dto->data)->toHaveCount(2);
    expect($dto->data[0]->entityType)->toBe(EntityType::USER);
    expect($dto->data[0]->role)->toBe(AccessRole::OWNER);
    expect($dto->data[1]->entityType)->toBe(EntityType::TEAM);
    expect($dto->data[1]->role)->toBe(AccessRole::EDITOR);
});

it('can create library sharing', function () {
    Saloon::fake([
        CreateSharing::class => MockResponse::fixture('libraries/create-sharing'),
    ]);

    $response = $this->mistral->libraries()->createSharing(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        sharing: new SharingIn(
            entityId: '750e8400-e29b-41d4-a716-446655440002',
            entityType: EntityType::TEAM,
            role: AccessRole::EDITOR
        )
    );

    $dto = $response->dto();

    expect($dto->entityId)->toBe('750e8400-e29b-41d4-a716-446655440002');
    expect($dto->entityType)->toBe(EntityType::TEAM);
    expect($dto->role)->toBe(AccessRole::EDITOR);
});

it('can delete library sharing', function () {
    Saloon::fake([
        DeleteSharing::class => MockResponse::make(status: 204),
    ]);

    $response = $this->mistral->libraries()->deleteSharing(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        sharing: new SharingDelete(
            entityId: '750e8400-e29b-41d4-a716-446655440002',
            entityType: EntityType::TEAM
        )
    );

    expect($response->status())->toBe(204);
});

it('handles document status transitions', function () {
    Saloon::fake([
        GetDocument::class => MockResponse::fixture('libraries/document-processing'),
    ]);

    $response = $this->mistral->libraries()->getDocument(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        documentId: '650e8400-e29b-41d4-a716-446655440001'
    );

    $dto = $response->dto();

    expect($dto)->toHaveProperty('processingStatus')
        ->and($dto->processingStatus)->toBe('Processing');
});

it('handles failed document status', function () {
    Saloon::fake([
        GetDocument::class => MockResponse::fixture('libraries/document-failed'),
    ]);

    $response = $this->mistral->libraries()->getDocument(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        documentId: '650e8400-e29b-41d4-a716-446655440001'
    );

    $dto = $response->dto();

    expect($dto)->toHaveProperty('processingStatus')
        ->and($dto->processingStatus)->toBe('Failed');
});

it('handles different access roles', function () {
    Saloon::fake([
        ListSharing::class => MockResponse::fixture('libraries/list-sharing-roles'),
    ]);

    $response = $this->mistral->libraries()->listSharing('550e8400-e29b-41d4-a716-446655440000');
    $dto = $response->dto();

    expect($dto->data)->toHaveCount(3);
    expect($dto->data[0]->role)->toBe(AccessRole::OWNER);
    expect($dto->data[1]->role)->toBe(AccessRole::EDITOR);
    expect($dto->data[2]->role)->toBe(AccessRole::VIEWER);
});
