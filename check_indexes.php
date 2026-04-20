<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function checkIndexes($table) {
    try {
        $indexes = DB::select("SHOW INDEX FROM `$table` ");
        echo "Indexes for $table:\n";
        foreach($indexes as $index) {
            echo " - " . $index->Key_name . " (" . $index->Column_name . ")\n";
        }
    } catch (\Exception $e) {
        echo "Error checking $table: " . $e->getMessage() . "\n";
    }
}

checkIndexes('documents');
checkIndexes('users');
checkIndexes('document_status_logs');
