<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $results = DB::select('SELECT DATABASE() as db');
    echo "Connected to: " . $results[0]->db . "\n";
    
    $tables = DB::select('SHOW TABLES');
    echo "Tables count: " . count($tables) . "\n";
    foreach($tables as $table) {
        $array = (array)$table;
        echo " - " . reset($array) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
