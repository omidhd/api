<?php
/*
 * Description: RestFull Api Example
 * Author: OMID HADINEZHAD
*/
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, api_key");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Connect to database
include_once '../db.php';
$database = new Database();
$pdo = $database->getConnection();

// Api Key check
define("API_KEY", "12345"); 

if (!isset($_SERVER['HTTP_API_KEY']) || $_SERVER['HTTP_API_KEY'] !== API_KEY) {
    echo json_encode(["error" => "Invalid API Key"]);
    http_response_code(403); // Forbidden
    exit();
}


// Request method check
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            getUser($id);
        } else {
            getAllUsers();
        }
        break;

    case 'POST':
        addUser();
        break;

    case 'PUT':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            updateUser($id);
        } else {
            echo json_encode(["error" => "ID is required to update"]);
            http_response_code(400); // Bad Request
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            deleteUser($id);
        } else {
            echo json_encode(["error" => "ID required to delete"]);
            http_response_code(400);
        }
        break;

    case 'OPTIONS':
        echo json_encode(["success" => "Valid methods : GET, POST, PUT, DELETE, OPTIONS"]);
        http_response_code(200); // Success
        break;
        
    default:
        echo json_encode(["error" => "Invalid method"]);
        http_response_code(405); // Method Not Allowed
        break;
}

// Get one user
function getUser($id) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch();

    if ($user) {
        echo json_encode($user);
        http_response_code(200); 
    } else {
        echo json_encode(["error" => "User not found"]);
        http_response_code(404); // Not found
    }
}

// Get All user
function getAllUsers() {
    global $pdo;

    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll();

    if ($users) {
        echo json_encode($users);
        http_response_code(200);
    } else {
        echo json_encode(["error" => "No user found"]);
        http_response_code(404);
    }
}

// Add user
function addUser() {
    global $pdo;
    $input = json_decode(file_get_contents("php://input"), true);

    // Check information
    if (!isset($input['name']) || !isset($input['email'])) {
        echo json_encode(["error" => "Invalid information"]);
        http_response_code(400);
        return;
    }

    // Check exists email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $input['email']]);
    if ($stmt->fetch()) {
        echo json_encode(["error" => "This email is already registered"]);
        http_response_code(409); // Conflict
        return;
    }

    // Add new user
    $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (:name, :email)");
    $stmt->execute([
        'name' => $input['name'],
        'email' => $input['email']
    ]);

    echo json_encode(["success" => "User successfully added"]);
    http_response_code(201); // Created

}

// Update user
function updateUser($id) {
    global $pdo;
    $input = json_decode(file_get_contents("php://input"), true);

    // Check information
    if (!isset($input['name']) || !isset($input['email'])) {
        echo json_encode(["error" => "Invalid information"]);
        http_response_code(400);
        return;
    }

    // Check exists id
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    if (!$stmt->fetch()) {
        echo json_encode(["error" => "No user found"]);
        http_response_code(404);
        return;
    }

    // Check exists email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND id != :id");
    $stmt->execute(['email' => $input['email'], 'id' => $id]);
    if ($stmt->fetch()) {
        echo json_encode(["error" => "This email is already registered"]);
        http_response_code(409); // Conflict
        return;
    }

    // Update user information
    $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
    $stmt->execute([
        'name' => $input['name'],
        'email' => $input['email'],
        'id' => $id
    ]);

    echo json_encode(["success" => "User updated successfully"]);
    http_response_code(200); // OK
}


// Delete user
function deleteUser($id) {
    global $pdo;

    // Check exists id
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    if (!$stmt->fetch()) {
        echo json_encode(["error" => "User not found"]);
        http_response_code(404);
        return;
    }

    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);

    echo json_encode(["success" => "User deleted successfully"]);
    http_response_code(200); // OK
}