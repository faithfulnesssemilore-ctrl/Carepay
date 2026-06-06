<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
try {
    echo view('auth.forgot-password')->render();
    echo "\nOK\n";
} catch (Exception $e) {
    echo 'ERR: ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
