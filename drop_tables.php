<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$app = app();

try {
    if (Schema::hasTable('attachments')) {
        Schema::drop('attachments');
        echo "Dropped attachments table\n";
    }
    if (Schema::hasTable('lessons')) {
        Schema::drop('lessons');
        echo "Dropped lessons table\n";
    }
    echo "Tables dropped successfully\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
