<?php

require __DIR__ . '/Admin Panel/vendor/autoload.php';
$app = require_once __DIR__ . '/Admin Panel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "Connected successfully to: " . Illuminate\Support\Facades\DB::connection()->getDatabaseName() . "\n";
} catch (\Exception $e) {
    die("Could not connect to the database.  Please check your configuration. error:" . $e );
}
