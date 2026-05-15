<?php

namespace App\Services;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\GridFS\Bucket;
use MongoDB\BSON\ObjectId;

class MongoDBService
{
    protected Client $client;
    protected Database $database;

    public function __construct()
    {
        $uri = config('database.connections.mongodb.dsn');
        $dbName = config('database.connections.mongodb.database');

        $this->client = new Client($uri);
        $this->database = $this->client->selectDatabase($dbName);
    }

    /**
     * Get a MongoDB collection instance.
     */
    public function collection(string $name): Collection
    {
        return $this->database->selectCollection($name);
    }

    /**
     * Get the MongoDB database instance.
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * Get a GridFS bucket instance.
     */
    public function gridFSBucket(string $bucketName = 'photos'): Bucket
    {
        return $this->database->selectGridFSBucket([
            'bucketName' => $bucketName,
        ]);
    }

    /**
     * Upload a file to GridFS.
     *
     * @param string $filename  The filename to store in GridFS
     * @param string $contents  Raw file contents
     * @param array  $metadata  Optional metadata (e.g. content_type, category)
     * @return ObjectId  The GridFS file ID
     */
    public function gridFSUpload(string $filename, string $contents, array $metadata = []): ObjectId
    {
        $bucket = $this->gridFSBucket();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $contents);
        rewind($stream);

        $id = $bucket->uploadFromStream($filename, $stream, [
            'metadata' => (object) $metadata,
        ]);

        fclose($stream);

        return $id;
    }

    /**
     * Download a file from GridFS as raw string content.
     *
     * @param ObjectId|string $id  The GridFS file ID
     * @return array{contents: string, filename: string, metadata: object|null}
     */
    public function gridFSDownload(ObjectId|string $id): array
    {
        if (is_string($id)) {
            $id = new ObjectId($id);
        }

        $bucket = $this->gridFSBucket();
        $stream = $bucket->openDownloadStream($id);

        $contents = stream_get_contents($stream);
        $fileInfo = $bucket->getFileDocumentForStream($stream);

        fclose($stream);

        return [
            'contents' => $contents,
            'filename' => $fileInfo->filename ?? 'unknown',
            'metadata' => $fileInfo->metadata ?? null,
        ];
    }

    /**
     * Delete a file from GridFS.
     *
     * @param ObjectId|string $id  The GridFS file ID
     */
    public function gridFSDelete(ObjectId|string $id): void
    {
        if (is_string($id)) {
            $id = new ObjectId($id);
        }

        $this->gridFSBucket()->delete($id);
    }
}
