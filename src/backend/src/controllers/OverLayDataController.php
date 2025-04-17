<?php
namespace App\Controllers;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Expose-Headers: Content-Range");

use App\Models\OverLayData;
use Exception;

require_once '../models/OverLayData.php';

class OverLayDataController{
    protected $overLayData;

    public function __construct()
    {
        $this->overLayData = new OverLayData();
    }
    public function saveImageOverlayData($data) {
        header("Access-Control-Expose-Headers: Content-Range");
        if (!isset($data['postId'], $data['mediaType'], $data['mediaUrl'], $data['coordinates'], $data['reviewerId'])) {
            throw new Exception('Missing required fields');
        }

        $result = $this->overLayData->insertImageOverlayData($data);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Overlay data saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save overlay data']);
        }
    }

    public function saveVideoOverlayData($data) {
        header("Access-Control-Expose-Headers: Content-Range");
        if (!isset($data['postId'], $data['mediaType'], $data['mediaUrl'], $data['coordinates'], $data['reviewerId'])) {
            throw new Exception('Missing required fields');
        }

        $result = $this->overLayData->insertVideoOverlayData($data);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Overlay data saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save overlay data']);
        }
    }
    
    public function fetchOverLayData($postId)
    {
        header('Content-Type: application/json');
        $overLayData = $this->overLayData->fetchOverLayData($postId);
        echo json_encode($overLayData);
    }

    public function fetchVideoOverLayData($postId)
    {
        header('Content-Type: application/json');
        $overLayData = $this->overLayData->fetchVideoOverLayData($postId);
        echo json_encode($overLayData);
    }
}