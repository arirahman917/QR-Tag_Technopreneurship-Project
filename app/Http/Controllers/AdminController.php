<?php

namespace App\Http\Controllers;

use App\Services\MongoDBService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MongoDB\BSON\ObjectId;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class AdminController extends Controller
{
    protected MongoDBService $mongo;

    public function __construct(MongoDBService $mongo)
    {
        $this->mongo = $mongo;
    }

    /**
     * Show admin dashboard - defaults to pets tab.
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    /**
     * API: Get all records for a category (pets, humans, items).
     */
    public function getRecords(Request $request, string $category)
    {
        $validCategories = ['pets', 'humans', 'items'];
        if (!in_array($category, $validCategories)) {
            return response()->json(['error' => 'Kategori tidak valid'], 400);
        }

        $collection = $this->mongo->collection($category);
        $records = $collection->find([], ['sort' => ['_id' => -1]])->toArray();

        $result = [];
        foreach ($records as $record) {
            $doc = (array) $record;
            $doc['_id'] = (string) $record['_id'];
            $result[] = $doc;
        }

        return response()->json($result);
    }

    /**
     * API: Store a new record.
     */
    public function store(Request $request, string $category)
    {
        $validCategories = ['pets', 'humans', 'items'];
        if (!in_array($category, $validCategories)) {
            return response()->json(['error' => 'Kategori tidak valid'], 400);
        }

        $data = $this->buildDocumentData($request, $category);

        // Generate unique QR token
        $data['qr_token'] = Str::uuid()->toString();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Handle photo upload to GridFS
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $contents = file_get_contents($file->getRealPath());

            $gridFSId = $this->mongo->gridFSUpload($filename, $contents, [
                'content_type' => $file->getMimeType(),
                'category' => $category,
                'original_name' => $file->getClientOriginalName(),
            ]);

            $data['photo_id'] = (string) $gridFSId;
            $data['photo'] = $filename;
        }

        $collection = $this->mongo->collection($category);
        $result = $collection->insertOne($data);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil ditambahkan!',
            'id' => (string) $result->getInsertedId(),
        ]);
    }

    /**
     * API: Update an existing record.
     */
    public function update(Request $request, string $category, string $id)
    {
        $validCategories = ['pets', 'humans', 'items'];
        if (!in_array($category, $validCategories)) {
            return response()->json(['error' => 'Kategori tidak valid'], 400);
        }

        $data = $this->buildDocumentData($request, $category);
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Handle photo upload to GridFS
        if ($request->hasFile('photo')) {
            // Delete old photo from GridFS if exists
            $oldRecord = $collection->findOne(['_id' => new ObjectId($id)]);
            if ($oldRecord && !empty($oldRecord['photo_id'])) {
                try {
                    $this->mongo->gridFSDelete($oldRecord['photo_id']);
                } catch (\Exception $e) {
                    // Old file may already be deleted, continue
                }
            }

            $file = $request->file('photo');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $contents = file_get_contents($file->getRealPath());

            $gridFSId = $this->mongo->gridFSUpload($filename, $contents, [
                'content_type' => $file->getMimeType(),
                'category' => $category,
                'original_name' => $file->getClientOriginalName(),
            ]);

            $data['photo_id'] = (string) $gridFSId;
            $data['photo'] = $filename;
        }

        $collection = $this->mongo->collection($category);

        try {
            $objectId = new ObjectId($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'ID tidak valid'], 400);
        }

        $collection->updateOne(
            ['_id' => $objectId],
            ['$set' => $data]
        );

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui!',
        ]);
    }

    /**
     * API: Delete a record.
     */
    public function destroy(string $category, string $id)
    {
        $validCategories = ['pets', 'humans', 'items'];
        if (!in_array($category, $validCategories)) {
            return response()->json(['error' => 'Kategori tidak valid'], 400);
        }

        $collection = $this->mongo->collection($category);

        try {
            $objectId = new ObjectId($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'ID tidak valid'], 400);
        }

        // Delete photo from GridFS if exists
        $record = $collection->findOne(['_id' => $objectId]);
        if ($record && !empty($record['photo_id'])) {
            try {
                $this->mongo->gridFSDelete($record['photo_id']);
            } catch (\Exception $e) {
                // File may already be deleted
            }
        }

        $collection->deleteOne(['_id' => $objectId]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus!',
        ]);
    }

    /**
     * Serve a file from GridFS.
     * Route: /file/{id}
     */
    public function serveFile(string $id)
    {
        try {
            $file = $this->mongo->gridFSDownload($id);
        } catch (\Exception $e) {
            abort(404, 'File tidak ditemukan.');
        }

        $contentType = 'application/octet-stream';
        if ($file['metadata'] && isset($file['metadata']->content_type)) {
            $contentType = $file['metadata']->content_type;
        }

        return response($file['contents'])
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', 'inline; filename="' . $file['filename'] . '"')
            ->header('Cache-Control', 'public, max-age=604800');
    }

    /**
     * API: Generate QR code for a record.
     * Returns a base64-encoded PNG image of the QR code.
     */
    public function generateQR(string $category, string $id)
    {
        $validCategories = ['pets', 'humans', 'items'];
        if (!in_array($category, $validCategories)) {
            return response()->json(['error' => 'Kategori tidak valid'], 400);
        }

        $collection = $this->mongo->collection($category);

        try {
            $objectId = new ObjectId($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'ID tidak valid'], 400);
        }

        $record = $collection->findOne(['_id' => $objectId]);

        if (!$record) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        // Ensure record has a qr_token
        $qrToken = (string) ($record['qr_token'] ?? '');
        if (empty($qrToken)) {
            $qrToken = Str::uuid()->toString();
            $collection->updateOne(
                ['_id' => $objectId],
                ['$set' => ['qr_token' => $qrToken]]
            );
        }

        // Build the customer URL that QR will point to
        $url = url("/tag/{$category}/{$qrToken}");

        $color = request()->query('color', 'black');

        // Generate QR code as raw PNG
        $options = new QROptions([
            'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
            'eccLevel' => \chillerlan\QRCode\Common\EccLevel::H,
            'scale' => 10,
            'outputBase64' => false,
            'bgColor' => [255, 255, 255],
            'drawLightModules' => true,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
        ]);

        $qrcode = new QRCode($options);
        $qrRaw = $qrcode->render($url);

        $qrGd = imagecreatefromstring($qrRaw);

        // If white, invert colors (black QR becomes white QR on black background)
        if ($color === 'white') {
            imagefilter($qrGd, IMG_FILTER_NEGATE);
        }

        $logoFilename = $color === 'white' ? 'logo_qr_white.png' : 'logo_qr_black.png';
        $logoPath = public_path('images/' . $logoFilename);

        if (file_exists($logoPath)) {
            $logoGd = imagecreatefrompng($logoPath);
            imagealphablending($qrGd, true);
            imagesavealpha($qrGd, true);

            $qrWidth = imagesx($qrGd);
            $qrHeight = imagesy($qrGd);
            $logoWidth = imagesx($logoGd);
            $logoHeight = imagesy($logoGd);

            // Scale logo to 25% of QR code width
            $newLogoWidth = $qrWidth * 0.30;
            $newLogoHeight = ($logoHeight / $logoWidth) * $newLogoWidth;

            $x = ($qrWidth - $newLogoWidth) / 2 - 12;
            $y = ($qrHeight - $newLogoHeight) / 2 + 12;

            // Background rectangle to make logo stand out
            $bgColor = $color === 'white' ? imagecolorallocate($qrGd, 0, 0, 0) : imagecolorallocate($qrGd, 255, 255, 255);
            $pad = 10;
            imagefilledrectangle($qrGd, $x - $pad, $y - $pad, $x + $newLogoWidth + $pad, $y + $newLogoHeight + $pad, $bgColor);

            imagecopyresampled($qrGd, $logoGd, $x, $y, 0, 0, $newLogoWidth, $newLogoHeight, $logoWidth, $logoHeight);
            imagedestroy($logoGd);
        }

        ob_start();
        imagepng($qrGd);
        $finalQrRaw = ob_get_clean();
        imagedestroy($qrGd);

        $qrImageBase64 = 'data:image/png;base64,' . base64_encode($finalQrRaw);

        return response()->json([
            'success' => true,
            'qr_image' => $qrImageBase64,
            'qr_token' => $qrToken,
            'url' => $url,
        ]);
    }

    /**
     * Build the document data array from request, based on category.
     * Strips empty optional fields to keep MongoDB documents clean.
     */
    private function buildDocumentData(Request $request, string $category): array
    {
        $data = [];

        switch ($category) {
            case 'pets':
                // Required
                $data['name'] = $request->input('name', '');
                $data['species'] = $request->input('species', '');
                $data['breed'] = $request->input('breed', '');
                $data['condition'] = $request->input('condition', '');
                $data['emergency_message'] = $request->input('emergency_message', '');
                $data['contact_phone'] = $request->input('contact_phone', '');

                // Optional - only include if not empty
                $optionalFields = ['color', 'distinctive_mark', 'age', 'nickname', 'behavior_notes', 'owner_area'];
                foreach ($optionalFields as $field) {
                    $value = $request->input($field);
                    if (!empty($value)) {
                        $data[$field] = $value;
                    }
                }
                break;

            case 'humans':
                // Required
                $data['name'] = $request->input('name', '');
                $data['condition'] = $request->input('condition', '');
                $data['emergency_message'] = $request->input('emergency_message', '');
                $data['contact_phone'] = $request->input('contact_phone', '');

                // Optional
                $optionalFields = ['age', 'physical_description', 'languages', 'general_area', 'notes'];
                foreach ($optionalFields as $field) {
                    $value = $request->input($field);
                    if (!empty($value)) {
                        $data[$field] = $value;
                    }
                }
                break;

            case 'items':
                // Required
                $data['item_name'] = $request->input('item_name', '');
                $data['description'] = $request->input('description', '');
                $data['emergency_message'] = $request->input('emergency_message', '');
                $data['contact_phone'] = $request->input('contact_phone', '');

                // Optional
                $optionalFields = ['distinctive_mark', 'owner_area', 'reward', 'important_contents'];
                foreach ($optionalFields as $field) {
                    $value = $request->input($field);
                    if (!empty($value)) {
                        $data[$field] = $value;
                    }
                }
                break;
        }

        return $data;
    }
}
