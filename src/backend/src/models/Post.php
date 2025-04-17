<?php

namespace App\Models;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Expose-Headers: Content-Range");

use MongoDB\Client;
use Dotenv\Dotenv;
use PDO;
use PDOException;
use App\Controllers\ImageUpload;
use Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

class Post
{
    protected $client;
    protected $collection;
    private $pdo;

    public function __construct()
    {
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

        $mongoUrl = $_ENV['MONGODB_URL'];
        $this->client = new Client(uri: $mongoUrl);
        $this->imageCollection = $this->client->selectCollection(databaseName: $_ENV['MONGODB_DATABASE'], collectionName: 'images');
        $this->videoCollection = $this->client->selectCollection(databaseName: $_ENV['MONGODB_DATABASE'], collectionName: 'videos');

    }

    public function getPosts($range)
    {
        $skip = $range[0];
        $limit = $range[1] - $range[0] + 1;

        $stmt = $this->pdo->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM posts LIMIT :skip, :limit");
        $stmt->bindValue(':skip', $skip, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll();

        $total = $this->pdo->query("SELECT FOUND_ROWS()")->fetchColumn();

        header("Content-Range: posts $skip-" . ($skip + count($posts) - 1) . "/$total");
        header('Access-Control-Expose-Headers: Content-Range');

        return $posts;
    }

    public function getOne($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $post = $stmt->fetch();

        return $post;
    }

    public function getLastId()
    {
        return $this->pdo->lastInsertId();
    }

    public function createPost($data)
    {
        $userId = $_POST['userId'] ?? null;
        $title = $_POST['title'] ?? null;
        $body = $_POST['body'] ?? null;
        //$imageUrl = $data['imageUrl'] ?? null;

        if (isset($_FILES['file'])) {
            $file = $_FILES['file'];
        }

        if (empty($userId) || empty($title) || empty($body)) {
            throw new Exception("Missing required fields: userId, title, or body.");
        }

        $stmt = $this->pdo->prepare("INSERT INTO posts (userId, title, body) VALUES (:userId, :title, :body)");
        $stmt->execute([
            ':userId' => $userId,
            ':title' => $title,
            ':body' => $body
        ]);
        return $this->pdo->lastInsertId();
    }

    public function uploadImage($postId, $imagePath)
    {
        $imageUpload = new ImageUpload();
        $url = $imageUpload->uploadImage($imagePath, $postId);

        if ($url === null) {
            error_log("Failed to upload image for post ID: $postId");
        } else {
            error_log("Uploaded image for post ID: $postId, URL: $url");
        }

        return $url;
    }

    public function uploadVideo($postId, $videoPath)
    {
        $imageUpload = new ImageUpload();
        $url = $imageUpload->uploadVideo($videoPath, $postId);

        if ($url === null) {
            error_log("Failed to upload video for post ID: $postId");
        } else {
            error_log("Uploaded video for post ID: $postId, URL: $url");
        }

        return $url;
    }

    public function updatePostMediaUrls($postId, $mediaUrls)
    {
        $fields = [];
        $params = [':postId' => $postId];

        if (isset($mediaUrls['image'])) {
            $fields[] = 'imageUrl = :imageUrl';
            $params[':imageUrl'] = $mediaUrls['image'];
        }

        if (isset($mediaUrls['video'])) {
            $fields[] = 'videoUrl = :videoUrl';
            $params[':videoUrl'] = $mediaUrls['video'];
        }

        if (empty($fields)) {
            error_log('No media URLs provided for update.');
        }

        $sql = "UPDATE posts SET " . implode(', ', $fields) . " WHERE id = :postId";
        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
    }

    public function update($id, $data)
    {
        $fieldsToUpdate = [];
        $params = [':id' => $id];

        if (isset($data['title'])) {
            $fieldsToUpdate[] = 'title = :title';
            $params[':title'] = $data['title'];
        }

        if (isset($data['body'])) {
            $fieldsToUpdate[] = 'body = :body';
            $params[':body'] = $data['body'];
        }

        if (isset($data['imageUrl'])) {
            $fieldsToUpdate[] = 'imageUrl = :imageUrl';
            $params[':imageUrl'] = $data['imageUrl'];
        }

        if (isset($data['videoUrl'])) {
            $fieldsToUpdate[] = 'videoUrl = :videoUrl';
            $params[':videoUrl'] = $data['videoUrl'];
        }

        if (isset($data['assignedTo'])) {
            $fieldsToUpdate[] = 'assignedTo = :assignedTo';
            $params[':assignedTo'] = $data['assignedTo'];
        }

        if (empty($fieldsToUpdate)) {
            throw new Exception('No fields to update');
        }

        $sql = "
            UPDATE posts
            SET " . implode(', ', $fieldsToUpdate) . ", updatedAt = NOW()
            WHERE id = :id
        ";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function getMany($ids)
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $posts = $stmt->fetchAll();

        $total = count($posts);

        header("Content-Range: posts 0-" . ($total - 1) . "/$total");
        header('Access-Control-Expose-Headers: Content-Range');

        return $posts;
    }

    public function getPostsByAssignedTo($userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as postCount FROM posts WHERE assignedTo = :assignedTo");
        $stmt->bindValue(':assignedTo', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            return ['postCount' => 0];
        }
        header('Access-Control-Expose-Headers: Content-Range');
        header('Content-Type: application/json');
        return $result;
    }

    public function assignToReviewer($postId, $reviewerId) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE posts SET assignedTo = :reviewerId WHERE id = :postId");
            $stmt->bindValue(':reviewerId', $reviewerId, PDO::PARAM_INT);
            $stmt->bindValue(':postId', $postId, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $this->pdo->prepare("SELECT imageUrl, videoUrl FROM posts WHERE id = :postId");
            $stmt->bindValue(':postId', $postId, PDO::PARAM_INT);
            $stmt->execute();
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$post) {
                throw new Exception("Post not found");
            }

            $imageUrl = $post['imageUrl'];
            $videoUrl = $post['videoUrl'];
            
            $stmt = $this->pdo->prepare("INSERT INTO todos (postId, reviewerId, completed, imageUrl, videoUrl) VALUES (:postId, :reviewerId, 0, :imageUrl, :videoUrl)");
            $stmt->bindValue(':postId', $postId, PDO::PARAM_INT);
            $stmt->bindValue(':reviewerId', $reviewerId, PDO::PARAM_INT);
            $stmt->bindValue(':imageUrl', $imageUrl, PDO::PARAM_STR);
            $stmt->bindValue(':videoUrl', $videoUrl, PDO::PARAM_STR);
            $stmt->execute();

            $this->pdo->commit();

            return true;
        }
        catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
}