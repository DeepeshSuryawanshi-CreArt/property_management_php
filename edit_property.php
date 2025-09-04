<?php
require './vendor/autoload.php';
require './libs/database.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "my_key_kill";
$jwt = $_COOKIE['token'];
$user_id;

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
  echo json_encode([
    "success" => false,
    "message" => "Invalid or expired token",
    "error" => $e->getMessage()
  ]);
  header('location:login.html');
  exit();
}

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

// POST REQUEST HANDLE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // echo "<pre>";
  // var_dump($_POST);
  // echo "</pre>";
  // // property fields
  $name = $category = $type = $details = $address = $city = $country = $zip_code = $photo_path = null;
  $property_id = $_POST['id'];

  // Validation (same as your code)
  if (empty($_POST['name'])) {
    $errors['name'] = "Property name is required";
    $all_set = false;
  } else {
    $name = test_data($_POST['name']);
  }

  if (empty($_POST['category'])) {
    $errors['category'] = "Category is required";
    $all_set = false;
  } else {
    $category = test_data($_POST['category']);
  }

  if (empty($_POST['type'])) {
    $errors['type'] = "Type is required";
    $all_set = false;
  } else {
    $type = test_data($_POST['type']);
  }

  if (empty($_POST['details'])) {
    $errors['details'] = "Details are required";
    $all_set = false;
  } else {
    $details = test_data($_POST['details']);
  }

  if (empty($_POST['address'])) {
    $errors['address'] = "Address is required";
    $all_set = false;
  } else {
    $address = test_data($_POST['address']);
  }

  if (empty($_POST['city'])) {
    $errors['city'] = "City is required";
    $all_set = false;
  } else {
    $city = test_data($_POST['city']);
  }

  if (empty($_POST['country'])) {
    $errors['country'] = "Country is required";
    $all_set = false;
  } else {
    $country = test_data($_POST['country']);
  }

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

  // photo upload (keep existing if no new photo uploaded)
  // Keep current DB photo by default (so it remains if no new file uploaded)
  $photo_path = $_POST['photo'];

  // Handle uploaded photo (if any)
  if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['photo'];

    // basic upload error check
    if ($file['error'] !== UPLOAD_ERR_OK) {
      $errors['photo'] = "Error uploading photo (code {$file['error']})";
      $all_set = false;
    } else {
      // use finfo for reliable MIME detection
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      $mime = $finfo->file($file['tmp_name']);
      $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];

      if (!isset($allowed[$mime])) {
        $errors['photo'] = "Only JPG, PNG, GIF allowed";
        $all_set = false;
      } elseif ($file['size'] > 5 * 1024 * 1024) {
        $errors['photo'] = "File too large (max 5MB)";
        $all_set = false;
      } else {
        // Absolute directory where PHP will write the file
        $uploadDirAbs = __DIR__ . "/../uploads/properties/";
        if (!is_dir($uploadDirAbs) && !mkdir($uploadDirAbs, 0755, true)) {
          $errors['photo'] = "Cannot create upload directory";
          error_log("mkdir failed: $uploadDirAbs");
          $all_set = false;
        } else {
          // random filename + proper extension
          $ext = $allowed[$mime];
          try {
            $basename = bin2hex(random_bytes(8)); // random, safe
          } catch (Exception $e) {
            $basename = time() . '_' . bin2hex(substr(md5(uniqid('', true)), 0, 8));
          }
          $file_name = $basename . '.' . $ext;
          $target_file = $uploadDirAbs . $file_name;

          // move uploaded file
          if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // delete old file if exists and not same as new
            if (!empty($property['photos'])) {
              // convert stored relative path to absolute path for deletion
              $oldAbs = __DIR__ . "/../" . ltrim($property['photos'], '/');
              if (file_exists($oldAbs) && is_file($oldAbs)) {
                @unlink($oldAbs);
              }
            }

            // save relative path for DB (and for <img src="...">)
            $photo_path = "uploads/properties/" . $file_name;
          } else {
            $errors['photo'] = "Failed to move uploaded file";
            error_log("move_uploaded_file failed: tmp={$file['tmp_name']} target={$target_file}");
            $all_set = false;
          }
        }
      }
    }
  }


  // prepare params
  $params = [
    'id' => $property_id,  // IMPORTANT for update
    'name' => $name,
    'category' => $category,
    'type' => $type,
    'details' => $details,
    'address' => $address,
    'city' => $city,
    'country' => $country,
    'zip_code' => $zip_code,
    'photos' => $photo_path,
  ];

  if ($all_set) {
    // ✅ Update property instead of insert
    $result = $db->updateProperty($_POST['id'], $params);

    echo json_encode([
      'status' => $result['success'] ? 200 : 400,
      'success' => $result['success'],
      'errors' => $errors,
      'message' => $result['message']
    ]);
    // header('location:dashboard.php');
    exit();
  } else {
    echo json_encode([
      'status' => 422,
      'success' => false,
      'errors' => $errors,
      'message' => "Validation failed, please check input"
    ]);
    exit();
  }
}
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $property_id = $_GET['id'];
  // fetching hte data of property.
  $propert_result = $db->get_single_property($property_id);
  if (!$propert_result['success']) {
    echo 'someting went wrong.';
  }
  $property = $propert_result['data'][0];

}

?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>AdminLTE 2 | Edit Property</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
  <!-- Morris chart -->
  <link rel="stylesheet" href="bower_components/morris.js/morris.css">
  <!-- jvectormap -->
  <link rel="stylesheet" href="bower_components/jvectormap/jquery-jvectormap.css">
  <!-- Date Picker -->
  <link rel="stylesheet" href="bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="bower_components/bootstrap-daterangepicker/daterangepicker.css">
  <!-- bootstrap wysihtml5 - text editor -->
  <link rel="stylesheet" href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>

<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">

    <?php include 'components/header.php' ?>
    <?php include 'components/sidbar.php' ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          Dashboard
          <small>Control panel</small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
          <li class="active">Dashboard</li>
        </ol>
      </section>

      <!-- Main content -->
      <section class="content">
        <section class="content">
          <div class="row">
            <div class="col-md-12">
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Update Property</h3>
                </div>

                <!-- form start -->
                <form method="post" id="propertyForm">
                  <div class="box-body">

                    <div class="form-group">
                      <label for="propertyName">Property Name </label>
                      <input type="number" class="" name="id" value="<?php echo htmlspecialchars($property['id']) ?>">
                      <input type="text" class="form-control" id="propertyName" name="name"
                        value="<?php echo htmlspecialchars($property['name']) ?>" placeholder="Enter property name"
                        required>
                    </div>

                    <div class="form-group">
                      <label for="category">Category</label>
                      <select class="form-control" id="category" name="category" required>
                        <option value="">-- Select Category --</option>
                        <option value="apartment" <?= ($property['category'] === 'apartment') ? 'selected' : '' ?>>
                          Apartment</option>
                        <option value="penthouse" <?= ($property['category'] === 'penthouse') ? 'selected' : '' ?>>
                          Penthouse</option>
                        <option value="bungalow" <?= ($property['category'] === 'bungalow') ? 'selected' : '' ?>>Bungalow
                        </option>
                        <option value="residences" <?= ($property['category'] === 'residences') ? 'selected' : '' ?>>
                          Residences</option>
                        <option value="villa" <?= ($property['category'] === 'villa') ? 'selected' : '' ?>>Villa</option>
                      </select>
                    </div>


                    <div class="form-group">
                      <label for="type">Type</label>
                      <select class="form-control" id="type" name="type" required>
                        <option value="">-- Select type --</option>
                        <option value="rent" <?= ($property['type'] === 'rent') ? 'selected' : '' ?>>Rent</option>
                        <option value="sale" <?= ($property['type'] === 'sale') ? 'selected' : '' ?>>Sale</option>
                        <option value="other" <?= ($property['type'] === 'other') ? 'selected' : '' ?>>Other</option>
                      </select>
                    </div>


                    <div class="form-group">
                      <label for="photo">Property Photo</label>

                      <?php if (!empty($property['photos'])): ?>
                        <div style="margin-bottom:10px;">
                          <img src="./<?php echo htmlspecialchars($property['photos']); ?>" alt="Property Photo"
                            style="max-width: 200px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>
                      <?php endif; ?>

                      <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                      <p class="help-block">Upload a new image to replace the old one.</p>
                    </div>


                    <div class="form-group">
                      <label for="details">Property Details</label>
                      <textarea class="form-control" rows="4" id="details" name="details"
                        placeholder="Enter property details"><?php echo htmlspecialchars($property['details'] ?? '') ?></textarea>
                    </div>


                    <div class="form-group">
                      <label for="zipCode">Zip Code</label>
                      <input type="text" class="form-control" id="zipCode" name="zip_code"
                        value="<?php echo htmlspecialchars($property['zip_code']) ?>" placeholder="Enter zip code">
                    </div>

                    <div class="form-group">
                      <label for="address">Address</label>
                      <input type="text" class="form-control" id="address" name="address"
                        value="<?php echo htmlspecialchars($property['address']) ?>" placeholder="Enter the Address">
                    </div>

                    <div class="form-group">
                      <label for="city">City</label>
                      <input type="text" class="form-control" id="zipCode" name="city"
                        value="<?php echo htmlspecialchars($property['city']) ?>" placeholder="Enter the City.">
                    </div>

                    <div class="form-group">
                      <label for="country">Country</label>
                      <input type="text" class="form-control" id="zipCode"
                        value="<?php echo htmlspecialchars($property['country']) ?>" name="country"
                        placeholder="Enter the Country.">
                    </div>

                  </div>
                  <!-- /.box-body -->

                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                </form>

              </div>
            </div>
          </div>
        </section>

      </section>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <footer class="main-footer">
      <div class="pull-right hidden-xs">
        <b>Version</b> 2.4.18
      </div>
      <strong>Copyright &copy; 2014-2019 <a href="https://adminlte.io">AdminLTE</a>.</strong> All rights
      reserved.
    </footer>

    <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
  </div>
  <!-- ./wrapper -->

  <!-- jQuery 3 -->
  <script src="bower_components/jquery/dist/jquery.min.js"></script>
  <!-- jQuery UI 1.11.4 -->
  <script src="bower_components/jquery-ui/jquery-ui.min.js"></script>
  <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
  <script>
    $.widget.bridge('uibutton', $.ui.button);
  </script>
  <!-- Bootstrap 3.3.7 -->
  <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <!-- Morris.js charts -->
  <script src="bower_components/raphael/raphael.min.js"></script>
  <script src="bower_components/morris.js/morris.min.js"></script>
  <!-- Sparkline -->
  <script src="bower_components/jquery-sparkline/dist/jquery.sparkline.min.js"></script>
  <!-- jvectormap -->
  <script src="plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
  <script src="plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
  <!-- jQuery Knob Chart -->
  <script src="bower_components/jquery-knob/dist/jquery.knob.min.js"></script>
  <!-- daterangepicker -->
  <script src="bower_components/moment/min/moment.min.js"></script>
  <script src="bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
  <!-- datepicker -->
  <script src="bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
  <!-- Bootstrap WYSIHTML5 -->
  <script src="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
  <!-- Slimscroll -->
  <script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
  <!-- FastClick -->
  <script src="bower_components/fastclick/lib/fastclick.js"></script>
  <!-- AdminLTE App -->
  <script src="dist/js/adminlte.min.js"></script>
  <!-- my script -->
  <script src="assets/js/edit_property.js"></script>
  <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
  <script src="dist/js/pages/dashboard.js"></script>
  <!-- AdminLTE for demo purposes -->
  <script src="dist/js/demo.js"></script>
</body>

</html>