<?php

require __DIR__ . '/Admin Panel/vendor/autoload.php';
$app = require_once __DIR__ . '/Admin Panel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create(
    '/experts/5/book', 'POST',
    ['slot_id' => 9999, 'topic' => ''],
    [], [],
    ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
);

$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
