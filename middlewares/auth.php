<?php
require '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "my_key_kill";

$headers = apache_request_headers();

if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["message" => "No token provided"]);
    exit;
}

list($bearer, $jwt) = explode(" ", $headers['Authorization']);

try {
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    // ✅ Token is valid, user is authenticated
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'status'=>401,
        'success'=>false,
        "message" => "Invalid or expired token"
    ]);
    exit;
}