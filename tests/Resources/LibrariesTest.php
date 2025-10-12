<?php

use HelgeSverre\Mistral\Dto\Libraries\DocumentUpdateIn;
use HelgeSverre\Mistral\Dto\Libraries\LibraryIn;
use HelgeSverre\Mistral\Dto\Libraries\LibraryInUpdate;
use HelgeSverre\Mistral\Dto\Libraries\SharingDelete;
use HelgeSverre\Mistral\Dto\Libraries\SharingIn;
use HelgeSverre\Mistral\Enums\AccessRole;
use HelgeSverre\Mistral\Enums\DocumentStatus;
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
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    $this->mistral = new Mistral('test-api-key');
});

it('can list all libraries', function () {
    $mockClient = new MockClient([
        ListLibraries::class => MockResponse::fixture('libraries/list-libraries'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->list();
    $dto = $response->dto();

    expect($dto->data)->toHaveCount(2);
    expect($dto->data[0]->name)->toBe('Product Documentation');
    expect($dto->data[0]->description)->toBe('All product docs');
    expect($dto->data[1]->name)->toBe('Legal Library');
    expect($dto->total)->toBe(2);
});

it('can list libraries with pagination', function () {
    $mockClient = new MockClient([
        ListLibraries::class => MockResponse::fixture('libraries/list-libraries-paginated'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->list(page: 1, pageSize: 10);
    $dto = $response->dto();

    expect($dto->data)->toHaveCount(1);
    expect($dto->total)->toBe(15);
});

it('can create a library', function () {
    $mockClient = new MockClient([
        CreateLibrary::class => MockResponse::fixture('libraries/create-library'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->create(
        new LibraryIn(
            name: 'Product Documentation',
            description: 'All product docs for RAG'
        )
    );

    $dto = $response->dto();

    expect($dto->id)->toBe('550e8400-e29b-41d4-a716-446655440000');
    expect($dto->name)->toBe('Product Documentation');
    expect($dto->description)->toBe('All product docs for RAG');
    expect($dto->createdAt)->toBeString();
    expect($dto->updatedAt)->toBeString();
});

it('can get library details', function () {
    $mockClient = new MockClient([
        GetLibrary::class => MockResponse::fixture('libraries/get-library'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->get('550e8400-e29b-41d4-a716-446655440000');
    $dto = $response->dto();

    expect($dto->id)->toBe('550e8400-e29b-41d4-a716-446655440000');
    expect($dto->name)->toBe('Product Documentation');
    expect($dto->description)->toBe('All product docs for RAG');
});

it('can update a library', function () {
    $mockClient = new MockClient([
        UpdateLibrary::class => MockResponse::fixture('libraries/update-library'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->update(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        library: new LibraryInUpdate(
            name: 'Updated Documentation',
            description: 'Updated description'
        )
    );

    $dto = $response->dto();

    expect($dto->id)->toBe('550e8400-e29b-41d4-a716-446655440000');
    expect($dto->name)->toBe('Updated Documentation');
    expect($dto->description)->toBe('Updated description');
});

it('can delete a library', function () {
    $mockClient = new MockClient([
        DeleteLibrary::class => MockResponse::make(status: 204),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->delete('550e8400-e29b-41d4-a716-446655440000');

    expect($response->status())->toBe(204);
});

it('can list documents in a library', function () {
    $mockClient = new MockClient([
        ListDocuments::class => MockResponse::fixture('libraries/list-documents'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->listDocuments('550e8400-e29b-41d4-a716-446655440000');
    $dto = $response->dto();

    expect($dto->data)->toHaveCount(2);
    expect($dto->data[0]->name)->toBe('api-reference.pdf');
    expect($dto->data[0]->status)->toBe(DocumentStatus::PROCESSED);
    expect($dto->data[0]->libraryId)->toBe('550e8400-e29b-41d4-a716-446655440000');
    expect($dto->data[0]->sizeBytes)->toBe(1048576);
    expect($dto->data[0]->numChunks)->toBe(42);
});

it('can list documents with search', function () {
    $mockClient = new MockClient([
        ListDocuments::class => MockResponse::fixture('libraries/list-documents-search'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->listDocuments(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        search: 'API'
    );

    $dto = $response->dto();

    expect($dto->data)->toHaveCount(1);
    expect($dto->data[0]->name)->toBe('api-reference.pdf');
});

it('can list documents with pagination', function () {
    $mockClient = new MockClient([
        ListDocuments::class => MockResponse::fixture('libraries/list-documents-paginated'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->listDocuments(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        page: 2,
        pageSize: 5
    );

    $dto = $response->dto();

    expect($dto->data)->toHaveCount(5);
    expect($dto->total)->toBe(23);
});

it('can upload a document', function () {
    $mockClient = new MockClient([
        UploadDocument::class => MockResponse::fixture('libraries/upload-document'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->uploadDocument(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        filePath: __DIR__.'/../Fixtures/test-document.txt'
    );

    $dto = $response->dto();

    expect($dto->id)->toBe('650e8400-e29b-41d4-a716-446655440001');
    expect($dto->libraryId)->toBe('550e8400-e29b-41d4-a716-446655440000');
    expect($dto->name)->toBe('test-document.txt');
    expect($dto->status)->toBe(DocumentStatus::QUEUED);
});

it('can get document details', function () {
    $mockClient = new MockClient([
        GetDocument::class => MockResponse::fixture('libraries/get-document'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->getDocument(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        documentId: '650e8400-e29b-41d4-a716-446655440001'
    );

    $dto = $response->dto();

    expect($dto->id)->toBe('650e8400-e29b-41d4-a716-446655440001');
    expect($dto->libraryId)->toBe('550e8400-e29b-41d4-a716-446655440000');
    expect($dto->name)->toBe('api-reference.pdf');
    expect($dto->status)->toBe(DocumentStatus::PROCESSED);
    expect($dto->sizeBytes)->toBe(1048576);
    expect($dto->numChunks)->toBe(42);
});

it('can update document metadata', function () {
    $mockClient = new MockClient([
        UpdateDocument::class => MockResponse::fixture('libraries/update-document'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->updateDocument(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        documentId: '650e8400-e29b-41d4-a716-446655440001',
        update: new DocumentUpdateIn(name: 'renamed-document.pdf')
    );

    $dto = $response->dto();

    expect($dto->id)->toBe('650e8400-e29b-41d4-a716-446655440001');
    expect($dto->name)->toBe('renamed-document.pdf');
});

it('can delete a document', function () {
    $mockClient = new MockClient([
        DeleteDocument::class => MockResponse::make(status: 204),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->deleteDocument(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        documentId: '650e8400-e29b-41d4-a716-446655440001'
    );

    expect($response->status())->toBe(204);
});

it('can list sharing access for a library', function () {
    $mockClient = new MockClient([
        ListSharing::class => MockResponse::fixture('libraries/list-sharing'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->listSharing('550e8400-e29b-41d4-a716-446655440000');
    $dto = $response->dto();

    expect($dto->data)->toHaveCount(2);
    expect($dto->data[0]->entityType)->toBe(EntityType::USER);
    expect($dto->data[0]->role)->toBe(AccessRole::OWNER);
    expect($dto->data[1]->entityType)->toBe(EntityType::TEAM);
    expect($dto->data[1]->role)->toBe(AccessRole::EDITOR);
});

it('can create library sharing', function () {
    $mockClient = new MockClient([
        CreateSharing::class => MockResponse::fixture('libraries/create-sharing'),
    ]);

    $this->mistral->withMockClient($mockClient);

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
    $mockClient = new MockClient([
        DeleteSharing::class => MockResponse::make(status: 204),
    ]);

    $this->mistral->withMockClient($mockClient);

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
    $mockClient = new MockClient([
        GetDocument::class => MockResponse::fixture('libraries/document-processing'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->getDocument(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        documentId: '650e8400-e29b-41d4-a716-446655440001'
    );

    $dto = $response->dto();

    expect($dto->status)->toBe(DocumentStatus::PROCESSING);
});

it('handles failed document status', function () {
    $mockClient = new MockClient([
        GetDocument::class => MockResponse::fixture('libraries/document-failed'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->getDocument(
        libraryId: '550e8400-e29b-41d4-a716-446655440000',
        documentId: '650e8400-e29b-41d4-a716-446655440001'
    );

    $dto = $response->dto();

    expect($dto->status)->toBe(DocumentStatus::FAILED);
});

it('handles different access roles', function () {
    $mockClient = new MockClient([
        ListSharing::class => MockResponse::fixture('libraries/list-sharing-roles'),
    ]);

    $this->mistral->withMockClient($mockClient);

    $response = $this->mistral->libraries()->listSharing('550e8400-e29b-41d4-a716-446655440000');
    $dto = $response->dto();

    expect($dto->data)->toHaveCount(3);
    expect($dto->data[0]->role)->toBe(AccessRole::OWNER);
    expect($dto->data[1]->role)->toBe(AccessRole::EDITOR);
    expect($dto->data[2]->role)->toBe(AccessRole::VIEWER);
});
