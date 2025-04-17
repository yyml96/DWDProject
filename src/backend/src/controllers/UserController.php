<?php

namespace App\Controllers;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Expose-Headers: Content-Range");

use App\Models\User;
use Exception;

require_once '../models/User.php';

class UserController
{
    protected $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function index()
    {
        header('Content-Type: application/json');
        //header("Access-Control-Expose-Headers: Content-Range");
        $range = isset($_GET['range']) ? json_decode($_GET['range']) : [0, 9];
        $users = $this->user->getUsers($range);
        echo json_encode($users);
    }

    public function getOne($id)
    {
        header('Content-Type: application/json');
        $user = $this->user->getOne($id);
        echo json_encode($user);
    }

    public function getMany($ids)
    {
        header('Content-Type: application/json');
        header("Access-Control-Expose-Headers: Content-Range");
        $users = $this->user->getMany($ids);
        echo json_encode(['data' => $users]);
    }

    public function create()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->user->create($data); // 调用 MySQL 模型的创建方法

        echo json_encode($result);
    }

    // PUT /api/users/{id}
    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $result = $this->user->update($id, $data);
    
        $updatedUser = $this->user->getOne($id);
    
        echo json_encode($updatedUser);
    }

    // DELETE /api/users/{id}
    public function delete($id)
    {
        $result = $this->user->delete($id);
        echo json_encode($result);
    }

    public function getUserIdByFirebaseUid($firebase_uid)
    {
        $userId = $this->user->getUserIdByFirebaseUid($firebase_uid);
        echo json_encode($userId);
    }
}
