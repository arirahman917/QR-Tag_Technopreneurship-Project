<?php

namespace App\Console\Commands;

use App\Services\MongoDBService;
use Illuminate\Console\Command;
use MongoDB\BSON\ObjectId;

class MigratePhotosToGridFS extends Command
{
    protected $signature = 'photos:migrate-gridfs';
    protected $description = 'Migrate existing photos from local public storage to MongoDB GridFS';

    public function handle(MongoDBService $mongo): int
    {
        $categories = ['pets', 'humans', 'items'];
        $storagePath = storage_path('app/public');

        $totalMigrated = 0;
        $totalFailed = 0;
        $totalSkipped = 0;

        foreach ($categories as $category) {
            $collection = $mongo->collection($category);
            $records = $collection->find()->toArray();

            $count = count($records);
            $this->info("Processing '{$category}' — {$count} record(s)...");

            foreach ($records as $record) {
                $id = (string) $record['_id'];
                $photo = $record['photo'] ?? null;
                $photoId = $record['photo_id'] ?? null;

                // Skip if already migrated or no photo
                if (!empty($photoId)) {
                    $totalSkipped++;
                    continue;
                }

                if (empty($photo)) {
                    $totalSkipped++;
                    continue;
                }

                // Build the full file path - try multiple locations
                $pathVariants = [
                    $storagePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $photo),
                    public_path(str_replace('/', DIRECTORY_SEPARATOR, $photo)),
                    $storagePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, preg_replace('#^storage/#', '', $photo)),
                ];

                $filePath = null;
                foreach ($pathVariants as $variant) {
                    if (file_exists($variant)) {
                        $filePath = $variant;
                        break;
                    }
                }

                if (!$filePath) {
                    $this->warn("  ✕ [{$id}] File not found for: {$photo}");
                    $totalFailed++;
                    continue;
                }

                try {
                    $contents = file_get_contents($filePath);
                    $filename = basename($photo);

                    // Detect MIME type
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->buffer($contents);

                    // Upload to GridFS
                    $gridFSId = $mongo->gridFSUpload($filename, $contents, [
                        'content_type' => $mimeType,
                        'category' => $category,
                        'original_path' => $photo,
                    ]);

                    // Update the document with the new photo_id
                    $collection->updateOne(
                        ['_id' => new ObjectId($id)],
                        ['$set' => [
                            'photo_id' => (string) $gridFSId,
                            'photo' => $filename,
                        ]]
                    );

                    $this->line("  ✓ [{$id}] {$photo} → GridFS ({$gridFSId})");
                    $totalMigrated++;

                } catch (\Exception $e) {
                    $this->error("  ✕ [{$id}] Error: {$e->getMessage()}");
                    $totalFailed++;
                }
            }
        }

        $this->newLine();
        $this->info("═══════════════════════════════════");
        $this->info("  Migration Complete!");
        $this->info("  ✓ Migrated: {$totalMigrated}");
        $this->info("  → Skipped:  {$totalSkipped}");
        $this->info("  ✕ Failed:   {$totalFailed}");
        $this->info("═══════════════════════════════════");

        return self::SUCCESS;
    }
}
