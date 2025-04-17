<?php
namespace App\Controllers;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Expose-Headers: Content-Range");

use App\Models\Todo;
use Exception;

require_once '../models/Todo.php';

class TodoController {

    protected $todo;

    public function __construct()
    {
        $this->todo = new Todo();
    }

    public function index()
    {
        header('Content-Type: application/json');
        $range = isset($_GET['range']) ? json_decode($_GET['range']) : [0, 9];
        $todos = $this->todo->getTodos($range);
        echo json_encode($todos);
    }

    public function getOne($id)
    {
        header('Content-Type: application/json');
        $todo = $this->todo->getOne($id);
        echo json_encode($todo);
    }    

    public function getMany($ids)
    {
        header('Content-Type: application/json');
        header("Access-Control-Expose-Headers: Content-Range");
        $todos = $this->todo->getMany($ids);
        echo json_encode(['data' => $todos]);
    }
    
}
