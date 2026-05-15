<?php

use App\Services\MongoDBService;

$mongo = app(MongoDBService::class);
$col = $mongo->collection('auth_admin');

$admin = $col->findOne(['username' => 'admin']);

if (!$admin) {
    $col->insertOne([
        'username' => 'admin',
        'password' => password_hash('admin123', PASSWORD_BCRYPT),
        'name' => 'Administrator',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    echo "Admin created successfully! Username: admin | Password: admin123\n";
} else {
    echo "Admin already exists.\n";
}
