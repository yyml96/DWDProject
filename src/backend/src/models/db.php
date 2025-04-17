<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");

require '/var/www/html/backend/vendor/autoload.php';

use MongoDB\Client;
use MongoDB\Driver\ServerApi;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable('/var/www/html/backend');
$dotenv->load();

$url = $_ENV['MONGODB_URL'];
$databaseName = $_ENV['MONGODB_DATABASE'];
// Set the version of the Stable API on the client
$apiVersion = new ServerApi(ServerApi::V1);
// Create a new client and connect to the server
$options = [
    'serverApi' => $apiVersion,
    'ssl' => true,
];

try {
    // Create a new client and connect to the server
    $client = new Client($url, [], $options);

    // Send a ping to confirm a successful connection
    $client->selectDatabase('admin')->command(['ping' => 1]);
    //echo "Pinged your deployment. You successfully connected to MongoDB!\n";
} catch (Exception $e) {
    // Capture and print the exception message
    //echo "Failed to connect to MongoDB: " . $e->getMessage();
}

// MySQL connection test
$mysqlHost = $_ENV['MYSQL_HOSTNAME'];
$mysqlPort = $_ENV['MYSQL_PORT'];
$mysqlUser = $_ENV['MYSQL_USERNAME'];
$mysqlPass = $_ENV['MYSQL_PASSWORD'];
$mysqlSslMode = $_ENV['MYSQL_SSL_MODE'];
$mysqlDatabase = $_ENV['MYSQL_DB']; 

$con = mysqli_init();
mysqli_ssl_set($con, NULL, NULL, "/var/www/html/backend/DigiCertGlobalRootCA.crt.pem", NULL, NULL);
if (mysqli_real_connect($con, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort, MYSQLI_CLIENT_SSL)) {
    //echo "Successfully connected to MySQL!\n";
} else {
    //echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
?>
