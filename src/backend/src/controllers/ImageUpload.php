<?php

namespace App\Controllers;

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;
use Dotenv\Dotenv;
use MongoDB\BSON\ObjectId;
use Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class ImageUpload
{
    private $client;
    private $database;
    private $bucket;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $mongoUrl = $_ENV['MONGODB_URL'];
        $this->client = new Client($mongoUrl);
        $this->database = $_ENV['MONGODB_DATABASE'];
        $this->bucket = $this->client->selectDatabase($this->database)->selectGridFSBucket();
    }

    public function uploadImage($imagePath, $postId)
    {
        $stream = fopen($imagePath, 'rb'); 

        $fileId = $this->uploadToGridFS($stream, basename($imagePath));
        fclose($stream);

        $this->saveMediaReference($fileId, $postId, 'image');

        return "http://localhost:8098/backend/api/images/" . $fileId;
    }

    public function uploadVideo($videoPath, $postId)
    {
        try {
            $stream = fopen($videoPath, 'rb');
            if ($stream === false) {
                throw new Exception('Failed to open video file.');
            }
            
            $fileId = $this->uploadToGridFS($stream, basename($videoPath));
            fclose($stream);

            if ($fileId) {
                $this->saveMediaReference($fileId, $postId, 'video');
                return "http://localhost:8098/backend/api/videos/" . $fileId;
            } else {
                throw new Exception('Failed to upload video to GridFS');
            }
        } catch (Exception $e) {
            error_log("Video upload failed for post ID: $postId. Error: " . $e->getMessage());
            return null;
        }
    }

    private function saveMediaReference($fileId, $postId, $type)
    {
        $collection = $this->client->selectCollection($this->database, $type . 's');
        $url = "http://localhost:8098/backend/api/{$type}s/" . $fileId;

        $collection->insertOne([
            'postId' => $postId,
            'fileId' => $fileId,
            "{$type}_url" => $url,
            'uploadedAt' => new UTCDateTime()
        ]);
    }


    private function uploadToGridFS($stream, $fileName)
    {
        return $this->bucket->uploadFromStream($fileName, $stream);
    }

    public function getImagesByPostId($postId)
    {
        $collection = $this->client->selectCollection($this->database, 'images');
        return $collection->find(['postId' => $postId])->toArray(); 
    }

    public function getVideoByPostId($postId)
    {
        $collection = $this->client->selectCollection($this->database, 'videos');
        return $collection->find(['postId' => $postId])->toArray(); 
    }

    public function getImageById($fileId)
    {
        $mongoUrl = $_ENV['MONGODB_URL'];
        $mongoClient = new Client($mongoUrl);
        $bucket = $mongoClient->selectDatabase($_ENV['MONGODB_DATABASE'])->selectGridFSBucket();
        $objectId = new ObjectId($fileId);

        $stream = $bucket->openDownloadStream($objectId);

        $file = $this->bucket->findOne(['_id' => $objectId]);
        
        if ($file) {
            $extension = pathinfo($file->filename, PATHINFO_EXTENSION);

            switch (strtolower($extension)) {
                case 'jpg':
                case 'jpeg':
                    header('Content-Type: image/jpeg');
                    break;
                case 'png':
                    header('Content-Type: image/png');
                    break;
                case 'gif':
                    header('Content-Type: image/gif');
                    break;
                default:
                    header('Content-Type: application/octet-stream');
                    break;
            }

            fpassthru($stream);
            fclose($stream);
            exit;
        }else{

            http_response_code(404);
            echo json_encode(['error' => 'Image not found.']);
        }
    }

    public function getVideoById($fileId)
    {
        $mongoUrl = $_ENV['MONGODB_URL'];
        $mongoClient = new Client($mongoUrl);
        $bucket = $mongoClient->selectDatabase($_ENV['MONGODB_DATABASE'])->selectGridFSBucket();
        $objectId = new ObjectId($fileId);

        $stream = $bucket->openDownloadStream($objectId);

        $file = $this->bucket->findOne(['_id' => $objectId]);
        
        if ($file) {
            $extension = pathinfo($file->filename, PATHINFO_EXTENSION);

            switch (strtolower($extension)) {
                case 'mp4':
                    header('Content-Type: video/mp4');
                    break;
                case 'webm':
                    header('Content-Type: video/webm');
                    break;
                case 'ogg':
                    header('Content-Type: video/ogg');
                    break;
                default:
                    header('Content-Type: application/octet-stream');
                    break;
            }

            fpassthru($stream);
            fclose($stream);
            exit;
        }else{

            http_response_code(404);
            echo json_encode(['error' => 'Video not found.']);
        }
    }

/*
$imageUpload = new ImageUpload();
$postId = '1';
$fileId = $imageUpload->uploadImage('/var/www/html/backend/src/images/react.png', $postId);
echo "Uploaded image ID: $fileId";
*/
}