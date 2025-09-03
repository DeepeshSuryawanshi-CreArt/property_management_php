<?php
include '../libs/database.php';
require '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "my_key_kill";


$db = new database();
$all_set = true;
$user_email = $user_password = null;
$errors = [
    'email' => '',
    'password' => '',
    'system' => ''
];
function test_data($data)
{
    $data = trim($data);
    $data = stripcslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    //email
    if (empty($_POST['email'])) {
        $errors['email'] = 'Email is Empty';
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 300,
            'success' => false,
            'method' => 'POST',
            'message' => 'Email is not given.',
            'errors' => json_encode($errors)
        ]);
        exit();
    } else {
        $user_email = test_data($_POST['email']);
        if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Given Email is Not a Valid Email';
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 300,
                'success' => false,
                'method' => 'POST',
                'message' => 'Email is not Valid.',
                'errors' => json_encode($errors)
            ]);
            exit();
        }
    }
    // password
    if (empty($_POST['password'])) {
        $errors['password'] = "Password is Required";
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 300,
            'success' => false,
            'method' => 'POST',
            'message' => 'Password is not Given.',
            'errors' => json_encode($errors)
        ]);
        exit();
    } else {
        $user_password = $_POST['password'];
        if (strlen($user_password) < 8) {
            $errors['password'] = "Password must have at least 8 characters";
            $all_set = false;
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 300,
                'success' => false,
                'method' => 'POST',
                'message' => 'Wrong or Invalid Password',
                'errors' => json_encode($errors)
            ]);
            exit();
        }
    }
    // AUTHENTICATION LOGIN USER;
    $result = $db->login(user_email: $user_email, password: $user_password);
    if ($result['success']) {
        $payload = [
            "iss" => "http://localhost",   // issuer
            "aud" => "http://localhost",   // audience
            "iat" => time(),               // issued at
            "exp" => time() + (60 * 60),     // expires in 1 hour
            "data" => [
                "user_id"=> $result['user']['id'],
                "email" => $result['user']['email'],
            ]
        ];
        $jwt = JWT::encode(payload: $payload,key: $secret_key,alg: 'HS256');
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 200,
            'success' => true,
            'user' => $result['user'],
            'token'=>$jwt,
            'message' => $result['message']
        ]);
        exit();
    } else {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 300,
            'success' => false,
            'user' => "user Not Found!",
            'message' => $result['message']
        ]);
        exit();
    }

} else {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 200,
        'success' => true,
        'method' => 'GET',
        'message' => 'Get method call. Login'
    ]);
    exit();
}