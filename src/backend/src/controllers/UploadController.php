<?php
namespace App\Controllers;
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use PDO;
use PDOException;
use MongoDB\Client;

class UploadController {
    private $mongoClient;
    private $mysqlPdo;
    private $imageCollection;
    private $videoCollection;

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
            $this->mysqlPdo = new PDO($dsn, $mysqlUser, $mysqlPass, [
                PDO::MYSQL_ATTR_SSL_CA => '/var/www/html/backend/DigiCertGlobalRootCA.crt.pem',
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            
        }

        $mongoUrl = $_ENV['MONGODB_URL'];
        $this->mongoClient = new Client($mongoUrl);
        $this->imageCollection = $this->mongoClient->selectCollection($_ENV['MONGODB_DATABASE'], 'images');
        $this->videoCollection = $this->mongoClient->selectCollection($_ENV['MONGODB_DATABASE'], 'videos');
    }

    public function upload() {
        $fileUrls = [];

        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileType = $_FILES['image']['type'];

            $imageUpload = new ImageUpload();
            $fileId = $imageUpload->uploadImage($fileTmpPath, null);
            $fileUrls['imageUrl'] = "http://localhost/backend/api/images/{$fileId}";
        }

        if ($_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['video']['tmp_name'];
            $fileName = $_FILES['video']['name'];
            $fileType = $_FILES['video']['type'];

            $imageUpload = new ImageUpload();
            $fileId = $imageUpload->uploadVideo($fileTmpPath, null);
            $fileUrls['videoUrl'] = "http://localhost/backend/api/videos/{$fileId}";
        }

        if (empty($fileUrls)) {
            http_response_code(400);
            echo json_encode(['error' => 'File upload error']);
            return;
        }

        // 存储到 MySQL
        $stmt = $this->mysqlPdo->prepare("INSERT INTO files (name, type, url) VALUES (:name, :type, :url)");
        foreach ($fileUrls as $url) {
            $stmt->execute([
                ':name' => $fileName,
                ':type' => $fileType,
                ':url' => $url
            ]);
        }

        echo json_encode($fileUrls);
    }
}
