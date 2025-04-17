<?php

namespace App\Test;

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;
use Dotenv\Dotenv;
use MongoDB\BSON\ObjectId;

require_once __DIR__ . '/../../vendor/autoload.php';

class ImageUpload
{
    private $client;
    private $database;
    private $bucket;

    public function __construct()
    {
        // 从环境变量中获取 MongoDB 的连接 URL 和数据库名称
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $mongoUrl = $_ENV['MONGODB_URL'];
        $this->client = new Client($mongoUrl); // 创建 Client 实例
        $this->database = $_ENV['MONGODB_DATABASE']; // 初始化数据库名称
        $this->bucket = $this->client->selectDatabase($this->database)->selectGridFSBucket(); // 创建 GridFS 存储桶
    }

    public function uploadImage($imagePath, $postId)
    {
        // 打开图片文件的二进制流
        $stream = fopen($imagePath, 'rb'); 
        // 上传到 GridFS
        $fileId = $this->uploadToGridFS($stream, basename($imagePath));
        fclose($stream);

        // 保存 postId 和文件 ID 到 images 集合
        $this->saveImageReference($fileId, $postId, 'image');

        // 返回图片的访问 URL
        return "http://localhost:8098/backend/api/images/" . $fileId;
    }

    public function uploadVideo($videoPath, $postId)
    {
        // 打开视频文件的二进制流
        $stream = fopen($videoPath, 'rb'); 
        // 上传到 GridFS
        $fileId = $this->uploadToGridFS($stream, basename($videoPath));
        fclose($stream);

        // 保存 postId 和文件 ID 到 videos 集合
        $this->saveImageReference($fileId, $postId, 'video');

        // 返回视频的访问 URL
        return "http://localhost:8098/backend/api/videos/" . $fileId;
    }

    private function saveImageReference($fileId, $postId, $type)
    {
        $collection = $this->client->selectCollection($this->database, $type . 's');
        $url = "http://localhost:8098/backend/api/{$type}s/" . $fileId;

        $collection->insertOne([
            'postId' => $postId,
            'fileId' => $fileId,
            "{$type}_url" => $url,
            'uploadedAt' => new UTCDateTime() // 存储上传时间
        ]);
    }


    private function uploadToGridFS($stream, $fileName)
    {
        $fileId = $this->bucket->uploadFromStream($fileName, $stream);
        error_log("Uploaded file with ID: " . $fileId); // 打印文件 ID 以供调试
        return $fileId;
    }

    public function getImagesByPostId($postId)
    {
        // 根据 postId 查询所有图片
        $collection = $this->client->selectCollection($this->database, 'images');
        return $collection->find(['postId' => $postId])->toArray(); 
    }

    public function getImageById($fileId)
    {
        $mongoUrl = $_ENV['MONGODB_URL'];
$mongoClient = new Client($mongoUrl);
        $bucket = $mongoClient->selectDatabase($_ENV['MONGODB_DATABASE'])->selectGridFSBucket();
        // 转换 fileId 为 MongoDB 的 ObjectId
        $objectId = new ObjectId($fileId);

        // 从 GridFS 获取图像流
        $stream = $bucket->openDownloadStream($objectId);

        // 获取文件信息以确定 MIME 类型
        $file = $this->bucket->findOne(['_id' => $objectId]);
        
        if ($file) {
            $extension = pathinfo($file->filename, PATHINFO_EXTENSION);

            // 根据文件扩展名设置 Content-Type
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
                    header('Content-Type: application/octet-stream'); // 默认类型
                    break;
            }

            // 输出流内容
            fpassthru($stream);
            fclose($stream); // 关闭流
            exit; // 结束脚本执行
        }else{

        // 如果文件未找到，返回404
        http_response_code(404);
        echo json_encode(['error' => 'Image not found.']);
    }
}
}
// Usage example

$imageUpload = new ImageUpload();
$postId = '3'; // 替换为实际的 postId
$fileId = $imageUpload->uploadVideo('/var/www/html/backend/src/test_media/testVideo.mp4', $postId);
echo "Uploaded image ID: $fileId";
