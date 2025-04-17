<?php
use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use Dotenv\Dotenv;


require_once __DIR__ . '/../../vendor/autoload.php';
function getImageById($id) {

    $dotenv = Dotenv::createImmutable('/var/www/html/backend');
    $dotenv->load();

    $mongoUrl = $_ENV['MONGODB_URL'];
    $mongoClient = new Client($mongoUrl);
    $bucket = $mongoClient->selectDatabase($_ENV['MONGODB_DATABASE'])->selectGridFSBucket();

        try {
            // 获取图片的 ObjectId
            $fileId = new ObjectId($id);
            
            // 获取图像流
            $stream = $bucket->openDownloadStream($fileId);
            
            // 获取文件信息以确定 MIME 类型
            $file = $bucket->findOne(['_id' => $fileId]);
            
            if ($file) {
                // 根据文件扩展名设置 Content-Type
                $extension = pathinfo($file->filename, PATHINFO_EXTENSION);

                // 设置正确的响应头
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

                // 输出文件内容
                fpassthru($stream); // 将图片内容直接输出到浏览器
                fclose($stream); // 关闭流
                exit; // 终止脚本执行
            } else {
                // 如果找不到文件，返回404
                http_response_code(404);
                echo 'Image not found';
            }
        } catch (Exception $e) {
            // 捕获异常
            echo 'Error: ' . $e->getMessage();
        }
}