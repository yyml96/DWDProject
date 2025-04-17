<?php

namespace App\Models;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Expose-Headers: Content-Range");

use Dotenv\Dotenv;
use PDO;
use PDOException;

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

class Log
{
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
        } catch (PDOException $e) {
            //echo "Failed to connect to MySQL: " . $e->getMessage();
        }
    }

    public function store($data)
    {
        $payload = json_decode($data['payload'], true);
        
        $authorId = isset($data['author']['id']) ? $data['author']['id'] : null;
        $recordId = isset($payload['ids'][0]) ? $payload['ids'][0] : null;
        $changes = json_encode($payload);

        $stmt = $this->pdo->prepare("
            INSERT INTO audit_logs (resource, action, user_id, record_id, changes, created_at)
            VALUES (:resource, :action, :user_id, :record_id, :changes, NOW())
        ");
        
        $stmt->execute([
            ':resource' => $data['resource'],
            ':action' => $data['action'],
            ':user_id' => $authorId,
            ':record_id' => $recordId,
            ':changes' => $changes
        ]);

        return $this->pdo->lastInsertId();
    }

    public function getLogs($range)
    {
        $skip = $range[0];
        $limit = $range[1] - $range[0] + 1;

        $stmt = $this->pdo->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM audit_logs LIMIT :skip, :limit");
        $stmt->bindValue(':skip', $skip, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $logs = $stmt->fetchAll();

        $total = $this->pdo->query("SELECT FOUND_ROWS()")->fetchColumn();

        header("Content-Range: logs $skip-" . ($skip + count($logs) - 1) . "/$total");
        header('Access-Control-Expose-Headers: Content-Range');

        return $logs;
    }

    public function getOne($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM audit_logs WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $log = $stmt->fetch();

        return $log;
    }

}