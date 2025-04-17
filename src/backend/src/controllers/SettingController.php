<?php

namespace App\Controllers;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Expose-Headers: Content-Range");

use App\Models\Setting;

require_once '../models/Setting.php';

class SettingController {
    protected $setting;

    public function __construct() {
        $this->setting = new Setting;
    }

    public function getMaxAssignedPosts() {
        header('Content-Type: application/json');
        $result = $this->setting->getMaxAssignedPosts();

        echo json_encode($result);
    }

    public function setMaxAssignedPosts() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->setting->setMaxAssignedPosts($data);

        echo json_encode($result);
    }
}