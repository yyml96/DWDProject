<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Expose-Headers: Content-Range");

require_once '../models/db.php';

use App\Controllers\UserController;
use App\Controllers\TodoController;
use App\Controllers\PostController;
use App\Controllers\UploadController;
use App\Controllers\ImageUpload;
use App\Controllers\UserAuthController;
use App\Controllers\LogController;
use App\Controllers\SettingController;
use App\Controllers\OverlayDataController;

$userController = new UserController();
$todoController = new TodoController();
$postController = new PostController();
$uploadController = new UploadController();
$userAuthController = new UserAuthController();
$logController = new LogController();
$settingController = new SettingController();
$overlayController = new OverLayDataController();


$requestMethod = $_SERVER['REQUEST_METHOD'];
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriSegments = explode('/', $url);

//echo "Request URI: " . $uri . "\n";
//echo "Request Method: " . $requestMethod . "\n";

// Handle syncUser endpoint
if ($url === '/backend/api/syncUser' && $requestMethod === 'POST') {
    $userAuthController->syncUser();
}

// Handle getUserRole endpoint
if ($url === '/backend/api/getUserRole' && $requestMethod === 'GET') {
    $userAuthController->getUserRole();
}

// /backend/api/users routes
if ($url === '/backend/api/users' && $requestMethod === 'GET') {
    $userController->index();
} elseif ($url === '/backend/api/users' && $requestMethod === 'POST') {
    $userController->create();
} elseif (preg_match('/\/backend\/api\/users\/([^\/]+)$/', $url, $matches) && isset($matches[1])) {
    $userId = $matches[1];
    if ($requestMethod === 'GET') {
        $userController->getOne($userId);
    } elseif ($requestMethod === 'PUT') {
        $userController->update($userId);
    } elseif ($requestMethod === 'DELETE') {
        $userController->delete($userId);
    }
}

// /backend/api/todos routes
if ($url === '/backend/api/todos' && $requestMethod === 'GET') {
    $todoController->index();
} elseif ($url === '/backend/api/todos' && $requestMethod === 'POST') {
    $todoController->create();
} elseif (preg_match('/\/backend\/api\/todos\/([^\/]+)$/', $url, $matches) && isset($matches[1])) {
    $todoId = $matches[1];
    if ($requestMethod === 'GET') {
        $todoController->getOne($todoId);
    } elseif ($requestMethod === 'PUT') {
        $todoController->update($todoId);
    } elseif ($requestMethod === 'DELETE') {
        $todoController->delete($todoId);
    }
}

// /backend/api/posts routes
if ($url === '/backend/api/posts' && $requestMethod === 'GET') {
    $postController->index();
} elseif ($url === '/backend/api/posts' && $requestMethod === 'POST') {
    $postController->create();
} elseif (preg_match('/\/backend\/api\/posts\/([^\/]+)$/', $url, $matches) && isset($matches[1])) {
    $postId = $matches[1];
    if ($requestMethod === 'GET') {
        $postController->getOne($postId);
    } elseif ($requestMethod === 'PUT') {
        $postController->update($postId);
    } elseif ($requestMethod === 'DELETE') {
        $postController->delete($postId);
    }
}

// /backend/api/logs routes
if ($url === '/backend/api/logs' && $requestMethod === 'GET') {
    $logController->index();
} elseif (preg_match('/\/backend\/api\/logs\/([^\/]+)$/', $url, $matches) && isset($matches[1])) {
    $logId = $matches[1];
    if ($requestMethod === 'GET') {
        $logController->getOne($logId);
    }
}

// /backend/api/upload route for file upload
if ($url === '/backend/api/upload' && $requestMethod === 'POST') {
    $uploadController->upload();
}

// /backend/api/images/post/POST_ID route to get images by postId
if (preg_match('/\/backend\/api\/images\/post\/([^\/]+)$/', $url, $matches) && isset($matches[1])) {
    $postId = $matches[1];
    $imageUpload = new ImageUpload();
    $images = $imageUpload->getImagesByPostId($postId);

    header('Content-Type: application/json');
    echo json_encode($images);
}

// /backend/api/videos/post/POST_ID route to get videos by postId
if (preg_match('/\/backend\/api\/videos\/post\/([^\/]+)$/', $url, $matches) && isset($matches[1])) {
    $postId = $matches[1];
    $imageUpload = new ImageUpload();
    $videos = $imageUpload->getVideoByPostId($postId);

    header('Content-Type: application/json');
    echo json_encode($videos);
}

// /backend/api/images/FILE_ID route to get a single image by fileId
if (preg_match('/\/backend\/api\/images\/([^\/]+)$/', $url, $matches) && isset($matches[1])) {
    $fileId = $matches[1];
    $imageUpload = new ImageUpload();
    $imageUpload->getImageById($fileId);
}

// /backend/api/videos/FILE_ID route to get a single video by fileId
if (preg_match('/\/backend\/api\/videos\/([^\/]+)$/', $url, $matches) && isset($matches[1])) {
    $fileId = $matches[1];
    $imageUpload = new ImageUpload();
    $imageUpload->getVideoById($fileId);
}

// /backend/api/getUserIdByFirebaseUid route to get userId
if ($url === '/backend/api/getUserIdByFirebaseUid' && $requestMethod === 'GET') {
    $firebase_uid = $_GET['firebase_uid'];
    $userId = $userController->getUserIdByFirebaseUid($firebase_uid);
}

// /backend/api/posts/assignedTo route to get posts count by assignedTo
if ($url === '/backend/api/posts/assignedTo' && $requestMethod === 'GET') {
    $assignedTo = $_GET['assignedTo'];
    $postController->getPostsByAssignedTo($assignedTo);
}


// /backend/api/audit_logs route to store logs
if ($url === '/backend/api/audit_logs' && $requestMethod === 'POST') {
    $logController->store();
}

// /backend/api/settings/maxAssignedPosts route to set maxAssignedPosts
if ($url === '/backend/api/settings/maxAssignedPosts' && $requestMethod === 'PUT') {
    $settingController->setMaxAssignedPosts();
}

// /backend/api/settings/maxAssignedPosts route to get maxAssignedPosts
if ($url === '/backend/api/settings/maxAssignedPosts' && $requestMethod === 'GET') {
    $settingController->getMaxAssignedPosts();
}

// /backend/api/posts/assign/{id}
if (preg_match('/\/backend\/api\/posts\/assign\/([^\/]+)$/', $url, $matches) && $requestMethod === 'PUT') {
    $postId = $matches[1];
    $data = json_decode(file_get_contents('php://input'), true);
    $reviewerId = $data['reviewerId'];

    $postController->assignToReviewer($postId, $reviewerId);
}

// /backend/api/overlay/save route to save overlay data
if ($url === '/backend/api/overlay/imagesave' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $overlayController->saveImageOverlayData($data);
}

// /backend/api/overlay/save route to save overlay data
if ($url === '/backend/api/overlay/videosave' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $overlayController->saveVideoOverlayData($data);
}

if ($url === '/backend/api/overlay/fetchimage' && $requestMethod === 'GET') {
    $postId = $_GET['postId'] ?? null;
    if ($postId) {
        $overlayController->fetchOverlayData($postId);
    } else {
        echo json_encode(['coordinates' => []]);
    }
}

if ($url === '/backend/api/overlay/fetchvideo' && $requestMethod === 'GET') {
    $postId = $_GET['postId'] ?? null;
    if ($postId) {
        $overlayController->fetchVideoOverlayData($postId);
    } else {
        echo json_encode(['coordinates' => []]);
    }
}

?>