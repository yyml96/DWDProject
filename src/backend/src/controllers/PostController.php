<?php

namespace App\Controllers;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Expose-Headers: Content-Range");

use App\Models\Post;
use Exception;

require_once '../models/User.php';

class PostController
{
    protected $post;

    public function __construct()
    {
        $this->post= new Post();
    }

    public function index()
    {
        header('Content-Type: application/json');
        $range = isset($_GET['range']) ? json_decode($_GET['range']) : [0, 9];
        $posts = $this->post->getPosts($range);
        echo json_encode($posts);
    }

    public function getOne($id)
    {
        header('Content-Type: application/json');
        $post = $this->post->getOne($id);
        echo json_encode($post);
    }

    public function getMany($ids)
    {
        header('Content-Type: application/json');
        header("Access-Control-Expose-Headers: Content-Range");
        $posts = $this->post->getMany($ids);
        echo json_encode(['data' => $posts]);
    }

    public function create()
{
    header('Content-Type: application/json');
    $data = $_POST;
    $postId = $this->post->createPost($data);

    $uploadedUrls = [];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            $imageUrl = $this->post->uploadImage($postId, $_FILES['image']['tmp_name']);
            if ($imageUrl !== null) {
                $uploadedUrls['image'] = $imageUrl;
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        try {
            $videoUrl = $this->post->uploadVideo($postId, $_FILES['video']['tmp_name']);
            if ($videoUrl !== null) {
                $uploadedUrls['video'] = $videoUrl;
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    if (!empty($uploadedUrls)) {
        try {
            $this->post->updatePostMediaUrls($postId, $uploadedUrls);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    echo json_encode([
        'id' => $postId,
        'userId' => $data['userId'],
        'title' => $data['title'],
        'body' => $data['body'],
        'imageUrl' => $uploadedUrls['image'] ?? null,
        'videoUrl' => $uploadedUrls['video'] ?? null
    ]);
}

    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->post->update($id, $data);

        echo json_encode($result);
    }

    public function delete($id)
    {
        $result = $this->post->delete($id);
        echo json_encode($result);
    }

    public function getPostsByAssignedTo($userId) {
        $count = $this->post->getPostsByAssignedTo($userId);
        ob_start(); // 开始捕获输出
        $count = $this->post->getPostsByAssignedTo($userId);
        $output = ob_get_clean(); // 获取并清理输出缓存

        // 如果捕获的输出不为空，进行调试
        if ($output !== '') {
            error_log("Unexpected output: " . $output, 0);
        }
        header('Access-Control-Expose-Headers: Content-Range');
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode($count);
    }

    public function assignToReviewer($postId, $reviewerId) {
        $result = $this->post->assignToReviewer($postId, $reviewerId);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
}

