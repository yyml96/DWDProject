<?php

namespace App\Models;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Expose-Headers: Content-Range");

use Dotenv\Dotenv;
use PDO;
use Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

class Auth{
    protected $client;
    protected $collection;
    private $pdo;

    public function __construct() {
        $dotenv = Dotenv::createImmutable('/var/www/html/backend');
        $dotenv->load();

        $mysqlHost = $_ENV['MYSQL_HOSTNAME'];
        $mysqlPort = $_ENV['MYSQL_PORT'];
        $mysqlUser = $_ENV['MYSQL_USERNAME'];
        $mysqlPass = $_ENV['MYSQL_PASSWORD'];
        $mysqlDb = $_ENV['MYSQL_DB'];
        $mysqlSslMode = $_ENV['MYSQL_SSL_MODE'];

        $dsn = "mysql:host=$mysqlHost;port=$mysqlPort;dbname=$mysqlDb;charset=utf8mb4";
        try {
            $this->pdo = new PDO($dsn, $mysqlUser, $mysqlPass, [
                PDO::MYSQL_ATTR_SSL_CA => '/var/www/html/backend/DigiCertGlobalRootCA.crt.pem',
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            //echo "Successfully connected to MySQL!\n";
        } catch (Exception $e) {
            //echo "Failed to connect to MySQL: " . $e->getMessage();
        }
    }

    public function userExists($firebase_uid)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE firebase_uid = :firebase_uid");
        $stmt->execute(['firebase_uid' => $firebase_uid]);
        $user = $stmt->fetch();
        return $user;
    }

    public function syncUser($firebase_uid, $email)
    {
        if($this->userExists($firebase_uid)){
            return true;
        }
    }

    public function getUserRole($firebase_uid) {
        $stmt = $this->pdo->prepare("SELECT role FROM users WHERE firebase_uid = :firebase_uid");
        $stmt->execute(['firebase_uid' => $firebase_uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user['role'] : null;
    }
}
