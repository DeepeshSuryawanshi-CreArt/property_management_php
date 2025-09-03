<?php
include '../libs/database.php';
$db = new database();
$all_set = true;
$fname = $lname = $email = $mobile = $password = $address = $file_path = null;
$errors = [
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'mobile' => '',
    'address' => '',
    'password' => '',
    'file' => '',
    'system' => ''
];
function test_data($data)
{
    $data = trim($data);
    $data = stripcslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // firstname
    if (empty($_POST['firstname'])) {
        $errors['firstname'] = "firstname is Required";
        $all_set = false;
    } else {
        $fname = test_data($_POST['firstname']);
        if (!preg_match("/^[a-zA-Z-' ]*$/", $fname)) {
            $errors['firstname'] = "Only letters and white space allowed";
            $all_set = false;
        }
    }
    // lastname
    if (empty($_POST['lastname'])) {
        $errors['lastname'] = "firstname is Required";
        $all_set = false;
    } else {
        $lname = test_data($_POST['lastname']);
        if (!preg_match("/^[a-zA-Z-' ]*$/", $lname)) {
            $errors['lastname'] = "Only letters and white space allowed";
            $all_set = false;
        }
    }

    // email
    if (empty($_POST['email'])) {
        $errors['email'] = "Email is Required";
        $all_set = false;
    } else {
        $email = test_data($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid Email Address.";
            $all_set = false;
        }
    }

    // mobile
    if (empty($_POST['mobile'])) {
        $errors['mobile'] = "Mobile No. is Required";
        $all_set = false;
    } else {
        $mobile = test_data($_POST["mobile"]);
        if (strlen($mobile) > 10 or strlen($mobile) < 10) {
            $errors['mobile'] = "Invalid Mobile No.";
            $all_set = false;
        }
    }

    // address
    if (empty($_POST['address'])) {
        $errors['address'] = "Address Is require.";
    } else {
        $address = test_data($_POST['address']);
        if (strlen($address) < 5) {
            $errors['address'] = "Address must be at least 5 characters.";
            $all_set = false;
        }
    }
    // password
    if (empty($_POST['password'])) {
        $errors['password'] = "Password is Required";
        $all_set = false;
    } else {
        $password = $_POST['password'];
        if (strlen($password) < 8) {
            $errors['password'] = "Password must have at least 8 characters";
            $all_set = false;
        } else {
            // hash the password
            $password = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    if (!empty($_FILES['profile_image']['name'])) {
        if ($_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
            $errors['file'] = "Error uploading file.";
            $all_set = false;
        } else {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
                $errors['file'] = "Only JPG, PNG, GIF allowed.";
                $all_set = false;
            } else {
                if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
                    $errors['file'] = "File too large (max 2MB).";
                    $all_set = false;
                } else {

                    $target_dir = "../uploads/users/";
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }
                    $file_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
                    $target_file = $target_dir . $file_name;

                    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                        $file_path = $target_file;
                    } else {
                        $errors['file'] = "Could not save uploaded file.";
                        $all_set = false;
                    }
                }
            }
        }
    }
    
    $result = $db->register(
        table: 'users',
        params: [
            'firstname' => $fname,
            'lastname' => $lname,
            'email' => $email,
            'mobile' => $mobile,
            'address' => $address,
            'password' => $password,
            'profile_pic' => $file_path
        ]
    );
    // result of the db query
    if ($result) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'User registered successfully',
            'user_id' => $db->get_result()[0] ?? null
        ]);
    }
    else{
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'User not registered',
            'error'=> $db->get_result(),
            'user_id' => $db->get_result()[0] ?? null
        ]);

    }
    exit;
}