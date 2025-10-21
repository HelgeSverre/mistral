<?php

declare(strict_types=1);

namespace HelgeSverre\Mistral\Resource;

use HelgeSverre\Mistral\Dto\Libraries\DocumentUpdateIn;
use HelgeSverre\Mistral\Dto\Libraries\LibraryIn;
use HelgeSverre\Mistral\Dto\Libraries\LibraryInUpdate;
use HelgeSverre\Mistral\Dto\Libraries\SharingDelete;
use HelgeSverre\Mistral\Dto\Libraries\SharingIn;
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
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Libraries extends BaseResource
{
    /**
     * List all libraries
     */
    public function list(?int $page = null, ?int $pageSize = null): Response
    {
        return $this->connector->send(new ListLibraries($page, $pageSize));
    }

    /**
     * Create a new library
     */
    public function create(LibraryIn $library): Response
    {
        return $this->connector->send(new CreateLibrary($library));
    }

    /**
     * Get library details
     */
    public function get(string $libraryId): Response
    {
        return $this->connector->send(new GetLibrary($libraryId));
    }

    /**
     * Update library metadata
     */
    public function update(string $libraryId, LibraryInUpdate $library): Response
    {
        return $this->connector->send(new UpdateLibrary($libraryId, $library));
    }

    /**
     * Delete a library
     */
    public function delete(string $libraryId): Response
    {
        return $this->connector->send(new DeleteLibrary($libraryId));
    }

    /**
     * List documents in a library
     */
    public function listDocuments(
        string $libraryId,
        ?string $search = null,
        ?int $page = null,
        ?int $pageSize = null
    ): Response {
        return $this->connector->send(new ListDocuments($libraryId, $search, $page, $pageSize));
    }

    /**
     * Upload a document to a library
     */
    public function uploadDocument(string $libraryId, string $filePath): Response
    {
        return $this->connector->send(new UploadDocument($libraryId, $filePath));
    }

    /**
     * Get document details
     */
    public function getDocument(string $libraryId, string $documentId): Response
    {
        return $this->connector->send(new GetDocument($libraryId, $documentId));
    }

    /**
     * Update document metadata
     */
    public function updateDocument(
        string $libraryId,
        string $documentId,
        DocumentUpdateIn $update
    ): Response {
        return $this->connector->send(new UpdateDocument($libraryId, $documentId, $update));
    }

    /**
     * Delete a document from a library
     */
    public function deleteDocument(string $libraryId, string $documentId): Response
    {
        return $this->connector->send(new DeleteDocument($libraryId, $documentId));
    }

    /**
     * List sharing/access control for a library
     */
    public function listSharing(string $libraryId): Response
    {
        return $this->connector->send(new ListSharing($libraryId));
    }

    /**
     * Create or update library sharing/access
     */
    public function createSharing(string $libraryId, SharingIn $sharing): Response
    {
        return $this->connector->send(new CreateSharing($libraryId, $sharing));
    }

    /**
     * Delete library sharing/access
     */
    public function deleteSharing(string $libraryId, SharingDelete $sharing): Response
    {
        return $this->connector->send(new DeleteSharing($libraryId, $sharing));
    }
}
