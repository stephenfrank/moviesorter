<?php
use MovieSorter\Indexer;

require('vendor/autoload.php');

$dbName = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'index_db';

if (file_exists($dbName)) {
    unlink($dbName);
}

$indexer = new Indexer($dbName);
$indexer->rebuild();
