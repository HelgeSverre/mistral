<?php

namespace HelgeSverre\Mistral\Resource;

use HelgeSverre\Mistral\Dto\Files\ListFilesRequest;
use HelgeSverre\Mistral\Enums\FilePurpose;
use HelgeSverre\Mistral\Enums\SampleType;
use HelgeSverre\Mistral\Enums\Source;
use HelgeSverre\Mistral\Requests\Files\DeleteFile;
use HelgeSverre\Mistral\Requests\Files\DownloadFile;
use HelgeSverre\Mistral\Requests\Files\GetSignedUrl;
use HelgeSverre\Mistral\Requests\Files\ListFiles;
use HelgeSverre\Mistral\Requests\Files\RetrieveFile;
use HelgeSverre\Mistral\Requests\Files\UploadFile;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class Files extends BaseResource
{
    /**
     * Upload a file for fine-tuning
     *
     * @param  string  $filePath  The path to the file to upload
     * @param  FilePurpose|null  $purpose  The purpose of the file (fine-tune, batch)
     */
    public function upload(
        string $filePath,
        ?FilePurpose $purpose = null
    ): Response {
        return $this->connector->send(new UploadFile(
            filePath: $filePath,
            purpose: $purpose
        ));
    }

    /**
     * List all uploaded files
     *
     * @param  int|null  $page  The page number (default: 0)
     * @param  int|null  $pageSize  The page size (default: 100)
     * @param  SampleType|null  $sampleType  Filter by sample type
     * @param  Source|null  $source  Filter by source
     * @param  string|null  $search  Search query
     * @param  FilePurpose|null  $purpose  Filter by purpose
     */
    public function list(
        ?int $page = null,
        ?int $pageSize = null,
        ?SampleType $sampleType = null,
        ?Source $source = null,
        ?string $search = null,
        ?FilePurpose $purpose = null
    ): Response {
        return $this->connector->send(new ListFiles(
            new ListFilesRequest(
                page: $page,
                pageSize: $pageSize,
                sampleType: $sampleType,
                source: $source,
                search: $search,
                purpose: $purpose
            )
        ));
    }

    /**
     * Retrieve file metadata by file ID
     *
     * @param  string  $fileId  The UUID of the file
     */
    public function retrieve(string $fileId): Response
    {
        return $this->connector->send(new RetrieveFile($fileId));
    }

    /**
     * Delete a file by file ID
     *
     * @param  string  $fileId  The UUID of the file
     */
    public function delete(string $fileId): Response
    {
        return $this->connector->send(new DeleteFile($fileId));
    }

    /**
     * Download file content by file ID
     *
     * @param  string  $fileId  The UUID of the file
     * @return Response Response with binary content
     */
    public function download(string $fileId): Response
    {
        return $this->connector->send(new DownloadFile($fileId));
    }

    /**
     * Get temporary signed download URL for a file
     *
     * @param  string  $fileId  The UUID of the file
     * @param  int|null  $expiry  Hours before URL expires (default: 24)
     */
    public function getSignedUrl(string $fileId, ?int $expiry = null): Response
    {
        return $this->connector->send(new GetSignedUrl($fileId, $expiry));
    }
}
