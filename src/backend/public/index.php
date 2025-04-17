<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");


require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$mongoUser = $_ENV['MONGODB_USERNAME'];
$mongoPass = $_ENV['MONGODB_PASSWORD'];
$mongoCluster = $_ENV['MONGODB_CLUSTER'];
$mongoDatabase = $_ENV['MONGODB_DATABASE'];
$mongoURL = $_ENV['MONGODB_URL'];


$client = new MongoDB\Client(
    "$mongoURL"
);

require_once __DIR__ . '/var/www/html/backend/src/routes/api.php';
