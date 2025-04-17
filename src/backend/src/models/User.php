<?php

namespace App\Models;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Expose-Headers: Content-Range");

use Dotenv\Dotenv;
use PDO;
use PDOException;

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

class User
{
    protected $client;
    protected $collection;
    private $pdo;

    public function __construct() {
        $dotenv = Dotenv::createImmutable('/var/www/html/backend');
        $dotenv->load();

        $mysqlHost = $_ENV['MYSQL_HOSTNAME'];
        $mysqlPort = $_ENV['MYSQL_PORT'];
        $mysqlUser = $_ENV['MYSQL_USERNAME'];
        $mysqlPass = $_ENV['MYSQL_PASSWORD'];
        $mysqlDb = $_ENV['MYSQL_DB'];
        $mysqlSslMode = $_ENV['MYSQL_SSL_MODE'];

        $dsn = "mysql:host=$mysqlHost;port=$mysqlPort;dbname=$mysqlDb;charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $mysqlUser, $mysqlPass, [
                PDO::MYSQL_ATTR_SSL_CA => '/var/www/html/backend/DigiCertGlobalRootCA.crt.pem',
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            //echo "Successfully connected to MySQL!\n";
        } catch (PDOException $e) {
            //echo "Failed to connect to MySQL: " . $e->getMessage();
        }
    }

    public function getUsers($range)
    {
        $skip = $range[0];
        $limit = $range[1] - $range[0] + 1;

        $stmt = $this->pdo->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM users LIMIT :skip, :limit");
        $stmt->bindValue(':skip', $skip, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll();

        $total = $this->pdo->query("SELECT FOUND_ROWS()")->fetchColumn();

        header("Content-Range: users $skip-" . ($skip + count($users) - 1) . "/$total");
        header('Access-Control-Expose-Headers: Content-Range');

        return $users;
    }

    public function getOne($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getLastInsertedId()
    {
        return $this->pdo->lastInsertId();
    }

    public function create($data)
    {
        $sql = "INSERT INTO users (name, username, email, phone) VALUES (:name, :username, :email, :phone)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->execute();
        $id = $this->pdo->lastInsertId();
        return [
            'id' => $id,
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone']
        
        ];
    }

    public function update($id, $data)
    {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET name = :name, username = :username, email = :email, phone = :phone, role = :role, updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => (int) $id,
            ':name' => $data['name'],
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':role' => $data['role']
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount(); // 返回受影响的行数
    }

    public function getMany($ids)
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $users = $stmt->fetchAll();

        $total = count($users);

        header("Content-Range: users 0-" . ($total - 1) . "/$total");
        header('Access-Control-Expose-Headers: Content-Range');

        return $users;
    }

    public function getUserIdByFirebaseUid($firebase_uid) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE firebase_uid = :firebase_uid");
        $stmt->execute([':firebase_uid' => $firebase_uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user;
    }
}
