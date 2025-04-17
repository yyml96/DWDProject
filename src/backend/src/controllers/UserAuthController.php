<?php

namespace App\Controllers;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Expose-Headers: Content-Range");

use App\Models\Auth;
use Exception;

require_once '../models/Auth.php';

class UserAuthController
{
    protected $auth;
    public function __construct()
    {
        $this->auth = new Auth();
    }

    public function syncUser()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Invalid request method"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['uid']) && isset($data['email'])) {
            $firebase_uid = $data['uid'];
            $email = $data['email'];

            if ($this->auth->syncUser($firebase_uid, $email)) {
                $role = $this->auth->getUserRole($firebase_uid);
                echo json_encode(["message" => "User synced successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "User sync failed"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Invalid request data"]);
        }
    }

    public function getUserRole() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(["error" => "Invalid request method"]);
            return;
        }
    
        if (isset($_GET['uid'])) {
            $firebase_uid = $_GET['uid'];
    
            $role = $this->auth->getUserRole($firebase_uid);
            if ($role) {
                echo json_encode(["role" => $role]);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "User not found"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Invalid request data"]);
        }
    }
}    
