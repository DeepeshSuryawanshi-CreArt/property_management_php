<?php
require '../middlewares/auth.php';
require '../vendor/autoload.php';
require '../libs/database.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "my_key_kill";
$db = new database();
$all_set = true;
$user_id = null;
$errors = [
    'name' => '',
    'category' => '',
    'type' => '',
    'details' => '',
    'address' => '',
    'city' => '',
    'country' => '',
    'zip_code' => '',
    'photos' => '',
    'user_id' => '',
    'token' => '',
    'system' => ''
];
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


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // property fields
    $name = $category = $type = $details = $address = $city = $country = $zip_code = $photo_path = null;

    // name
    if (empty($_POST['name'])) {
        $errors['name'] = "Property name is required";
        $all_set = false;
    } else {
        $name = test_data($_POST['name']);
    }

    // category
    if (empty($_POST['category'])) {
        $errors['category'] = "Category is required";
        $all_set = false;
    } else {
        $category = test_data($_POST['category']);
    }

    // type
    if (empty($_POST['type'])) {
        $errors['type'] = "Type is required";
        $all_set = false;
    } else {
        $type = test_data($_POST['type']);
    }

    // details
    if (empty($_POST['details'])) {
        $errors['details'] = "Details are required";
        $all_set = false;
    } else {
        $details = test_data($_POST['details']);
    }

    // address
    if (empty($_POST['address'])) {
        $errors['address'] = "Address is required";
        $all_set = false;
    } else {
        $address = test_data($_POST['address']);
    }

    // city
    if (empty($_POST['city'])) {
        $errors['city'] = "City is required";
        $all_set = false;
    } else {
        $city = test_data($_POST['city']);
    }

    // country
    if (empty($_POST['country'])) {
        $errors['country'] = "Country is required";
        $all_set = false;
    } else {
        $country = test_data($_POST['country']);
    }

    // zip_code
    if (empty($_POST['zip_code'])) {
        $errors['zip_code'] = "Zip code is required";
        $all_set = false;
    } else {
        $zip_code = test_data($_POST['zip_code']);
        if (!preg_match("/^[0-9]{5,6}$/", $zip_code)) {
            $errors['zip_code'] = "Invalid zip code";
            $all_set = false;
        }
    }

    // property photo upload
    if (!empty($_FILES['photo']['name'])) {
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $errors['photo'] = "Error uploading photo";
            $all_set = false;
        } else {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['photo']['type'], $allowed_types)) {
                $errors['photo'] = "Only JPG, PNG, GIF allowed";
                $all_set = false;
            } else {
                if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
                    $errors['photo'] = "File too large (max 5MB)";
                    $all_set = false;
                } else {
                    // ✅ Use absolute path for moving
                    $target_dir = __DIR__ . "/../uploads/properties/";
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }

                    $file_name = time() . "_" . basename($_FILES["photo"]["name"]);
                    $target_file = $target_dir . $file_name;

                    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                        // ✅ Save relative path for DB
                        $photo_path = "uploads/properties/" . $file_name;
                    } else {
                        $errors['photos'] = "Could not save uploaded photo";
                        $all_set = false;

                        // ✅ Debug info
                        error_log("Upload failed: tmp_name=" . $_FILES["photo"]["tmp_name"] .
                            " target_file=" . $target_file);
                    }
                }
            }
        }
    }

    // user tokend decord 
    $params = [
        'name' => $name,
        'category' => $category,
        'type' => $type,
        'details' => $details,
        'address' => $address,
        'city' => $city,
        'country' => $country,
        'zip_code' => $zip_code,
        'photos' => $photo_path,
        'listed_by' => $user_id,
        'created_by' => $user_id
    ];

    if ($all_set) {
        //INSERTING THE DATA INTO DB.
        $result = $db->addProperty($params);
        if ($result['success']) {
            header('content-type:application/json');
            echo json_encode([
                'status' => 200,
                'sucess' => true,
                'errors' => $errors,
                'message' => $result['message']
            ]);
            exit();
        } else {
            header('content-type:application/json');
            echo json_encode([
                'status' => 400,
                'sucess' => false,
                'data' => $data,
                'errors' => $errors,
                'message' => $result['message']
            ]);
        }
    } else {
        header('content-type:application/json');
        echo json_encode([
            'status' => 500,
            'sucess' => false,
            'data' => null,
            'errors' => $errors,
            'message' => "An Error Occure, Please check and try Again."
        ]);
        exit();
    }
} else {
    header('content-type:application/json');
    echo json_encode([
        'status' => 200,
        'sucess' => true,
        'message' => 'Get Method Not alowed.'
    ]);
}