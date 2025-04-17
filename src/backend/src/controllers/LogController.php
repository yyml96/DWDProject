<?php

namespace App\Controllers;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Expose-Headers: Content-Range");

use App\Models\Log;

require_once '../models/Log.php';

class LogController {
    protected $log;

    public function __construct() {
        $this->log = new Log;
    }

    public function store() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->log->store($data);

        echo json_encode($result);
    }

    public function index() {
        header('Content-Type: application/json');
        $range = isset($_GET['range']) ? json_decode($_GET['range']) : [0, 9];
        $logs = $this->log->getLogs($range);
        echo json_encode($logs);
    }

    public function getOne($id) {
        header('Content-Type: application/json');
        $log = $this->log->getOne($id);
        echo json_encode($log);
    }
}