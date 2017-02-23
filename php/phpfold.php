<?php
require __DIR__.'/../vendor/autoload.php';

$path = $argv[1];
$folder = new Lvht\Phpfold\Folder();

echo json_encode($folder->fold($path));
