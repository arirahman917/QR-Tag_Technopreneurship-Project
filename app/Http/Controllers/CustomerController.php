<?php

namespace App\Http\Controllers;

use App\Services\MongoDBService;

class CustomerController extends Controller
{
    protected MongoDBService $mongo;

    public function __construct(MongoDBService $mongo)
    {
        $this->mongo = $mongo;
    }

    /**
     * Show the customer-facing identity page.
     * Route: /tag/{category}/{qr_token}
     * Finds the record by qr_token and renders the appropriate view.
     */
    public function show(string $category, string $qrToken)
    {
        $validCategories = ['pets', 'humans', 'items'];
        if (!in_array($category, $validCategories)) {
            abort(404, 'Kategori tidak ditemukan.');
        }

        $collection = $this->mongo->collection($category);
        $record = $collection->findOne(['qr_token' => $qrToken]);

        if (!$record) {
            abort(404, 'Data identitas tidak ditemukan.');
        }

        // Convert MongoDB document to plain array
        $data = $this->documentToArray($record);

        // Build photo URL from GridFS
        if (!empty($data['photo_id'])) {
            $data['photo_url'] = url('/file/' . $data['photo_id']);
        } else {
            $data['photo_url'] = null;
        }

        // Build WhatsApp URL
        if (!empty($data['contact_phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $data['contact_phone']);
            // Convert Indonesian format (08xx) to international (628xx)
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }
            $data['whatsapp_url'] = 'https://wa.me/' . $phone;
        } else {
            $data['whatsapp_url'] = '#';
        }

        return view("customer.{$category}", ['data' => $data]);
    }

    /**
     * Convert a MongoDB document (BSONDocument) to a plain PHP array.
     */
    private function documentToArray($document): array
    {
        $result = [];
        foreach ($document as $key => $value) {
            if ($key === '_id') {
                $result[$key] = (string) $value;
            } elseif ($value instanceof \MongoDB\Model\BSONArray) {
                $result[$key] = iterator_to_array($value);
            } elseif ($value instanceof \MongoDB\Model\BSONDocument) {
                $result[$key] = iterator_to_array($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
