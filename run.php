#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use MovieSorter\MovieSorter;
use MovieSorter\MovieTrainerCommand;
use Symfony\Component\Console\Application;

define('DB_NAME', __DIR__ . '/index_db');

$application = new Application();
$application->add(new StephenFrank\MovieSorter\FilesToFoldersCommand());
$application->add(new StephenFrank\MovieSorter\CleanFoldersCommand());
$application->run();