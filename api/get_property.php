<?php
require '../middlewares/auth.php';
require '../vendor/autoload.php';
require '../libs/database.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "my_key_kill";
$db = new database();
$all_set = true;
function test_data($data)
{
    $data = trim($data);
    $data = stripcslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

//  jwt working 
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';

if (!$authHeader) {
    header('content-type:app;ication/json');
    echo json_encode([
        "success" => false,
        "message" => "Authorization header missing"
    ]);
    exit();
}

list($bearer, $jwt) = explode(" ", $authHeader);

if ($bearer !== "Bearer" || !$jwt) {
    die(json_encode([
        "success" => false,
        "message" => "Invalid Authorization format"
    ]));
}

try {
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

    // Extract user data
    $user_id = $decoded->data->user_id ?? null;
    $user_email = $decoded->data->email ?? null;

    json_encode([
        "success" => true,
        "message" => "Token decoded successfully",
        "user_id" => $user_id,
        "email" => $user_email
    ]);
} catch (Exception $e) {
    header('content-type:app;ication/json');
    echo json_encode([
        "success" => false,
        "message" => "Invalid or expired token",
        "error" => $e->getMessage()
    ]);
    exit();
}

// get page from request (default = 1)
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;

$response = $db->getProperties($page, $limit);

header('Content-Type: application/json');
echo json_encode($response);