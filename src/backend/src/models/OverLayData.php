<?php

namespace App\Models;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Expose-Headers: Content-Range");

use Dotenv\Dotenv;
use PDO;
use PDOException;
use Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

class OverLayData
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

    public function insertImageOverlayData($data) {
        $sql = "INSERT INTO overlay_data (postId, mediaType, mediaUrl, coordinates, timestamp, description, reviewerId)
                VALUES (:postId, :mediaType, :mediaUrl, :coordinates, :timestamp, :description, :reviewerId)";
    
        $stmt = $this->pdo->prepare($sql);
    
        $stmt->bindValue(':postId', $data['postId'], PDO::PARAM_INT);
        $stmt->bindValue(':mediaType', $data['mediaType'], PDO::PARAM_STR);
        $stmt->bindValue(':mediaUrl', $data['mediaUrl'], PDO::PARAM_STR);
        $stmt->bindValue(':coordinates', json_encode($data['coordinates']), PDO::PARAM_STR);
        $stmt->bindValue(':timestamp', $data['timestamp'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
        $stmt->bindValue(':reviewerId', $data['reviewerId'], PDO::PARAM_INT);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function fetchOverlayData($postId)
    {
        $stmt = $this->pdo->prepare("
            SELECT coordinates, description 
            FROM overlay_data 
            WHERE postId = :postId AND mediaType = 'image'
        ");
        $stmt->bindValue(':postId', $postId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {

            try {
                $coordinates = json_decode($result['coordinates'], true);
                if (is_array($coordinates)) {
                    $result['coordinates'] = array_map(
                        fn($line) => $line['points'],
                        $coordinates
                    );
                } else {
                    $result['coordinates'] = [];
                }
            } catch (Exception $e) {
                $result['coordinates'] = [];
            }
        } else {
            $result = ['coordinates' => [], 'description' => null];
        }

        return $result;
    }

    public function insertVideoOverlayData($data) {
        $sql = "INSERT INTO overlay_data (postId, mediaType, mediaUrl, coordinates, timestamp, description, reviewerId)
                VALUES (:postId, :mediaType, :mediaUrl, :coordinates, :timestamp, :description, :reviewerId)";
    
        $stmt = $this->pdo->prepare($sql);
    
        $stmt->bindValue(':postId', $data['postId'], PDO::PARAM_INT);
        $stmt->bindValue(':mediaType', $data['mediaType'], PDO::PARAM_STR);
        $stmt->bindValue(':mediaUrl', $data['mediaUrl'], PDO::PARAM_STR);
        $stmt->bindValue(':coordinates', json_encode($data['coordinates']), PDO::PARAM_STR);
        $stmt->bindValue(':timestamp', $data['timestamp'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
        $stmt->bindValue(':reviewerId', $data['reviewerId'], PDO::PARAM_INT);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function fetchVideoOverlayData($postId)
    {
        $stmt = $this->pdo->prepare("
            SELECT coordinates, description 
            FROM overlay_data 
            WHERE postId = :postId AND mediaType = 'video'
        ");
        $stmt->bindValue(':postId', $postId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            
            try {
                $coordinates = json_decode($result['coordinates'], true);
                if (is_array($coordinates)) {
                    
                    $result['coordinates'] = array_map(
                        fn($line) => [
                            'tool' => $line['tool'] ?? 'pen', 
                            'points' => $line['points'] ?? [],
                            'timestamp' => $line['timestamp'] ?? 0,
                        ],
                        $coordinates
                    );
                } else {
                    $result['coordinates'] = []; 
                }
            } catch (Exception $e) {
                $result['coordinates'] = []; 
            }
        } else {
            $result = ['coordinates' => [], 'description' => null]; 
        }

        return $result;
    }

}
