<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>AdminLTE 2 | Registration Page</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="./bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="./bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="./bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="./dist/css/AdminLTE.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="./plugins/iCheck/square/blue.css">

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

<body class="hold-transition register-page">
  <div class="register-box">
    <div class="register-logo">
      <a href="./dashboard.php"><b>Admin</b>LTE</a>
    </div>

    <div class="register-box-body">
      <h3 class="login-box-msg">Register a new membership</h3>

      <form  method="post" id="registerForm" enctype="multipart/form-data">
        <!-- Firstname -->
        <div class="form-group has-feedback">
          <input type="text" name="firstname" class="form-control" placeholder="First Name" required>
          <span class="glyphicon glyphicon-user form-control-feedback"></span>
        </div>

        <!-- Lastname -->
        <div class="form-group has-feedback">
          <input type="text" name="lastname" class="form-control" placeholder="Last Name" required>
          <span class="glyphicon glyphicon-user form-control-feedback"></span>
        </div>

        <!-- Email -->
        <div class="form-group has-feedback">
          <input type="email" name="email" class="form-control" placeholder="Email" required>
          <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
        </div>
        <!-- mobile -->
        <div class="form-group has-feedback">
          <input type="number" name="mobile" class="form-control" placeholder="mobile no." required>
          <span class="glyphicon glyphicon-phone form-control-feedback"></span>
        </div>
        <!-- Address -->
        <div class="form-group has-feedback">
          <input type="text" name="address" class="form-control" placeholder="Address" required>
          <span class="glyphicon glyphicon-home form-control-feedback"></span>
        </div>

        <!-- Password -->
        <div class="form-group has-feedback">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
          <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>

        <!-- Profile Picture -->
        <div class="form-group has-feedback">
          <input type="file" name="profile_image" class="form-control" accept="image/*" required>
          <span class="glyphicon glyphicon-picture form-control-feedback"></span>
        </div>

        <!-- Terms & Register Button -->
        <div class="row">
          <div class="col-xs-8">
            <div class="checkbox icheck">
              <label>
                <input type="checkbox" name="terms" required> I agree to the <a href="#">terms</a>
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-xs-4">
            <button type="submit" class="btn btn-primary btn-block btn-flat">Register</button>
          </div>
          <!-- /.col -->
        </div>
      </form>


      <a href="login.html" class="text-center">I already have a membership</a>
    </div>
    <!-- /.form-box -->
  </div>
  <!-- /.register-box -->

  <!-- jQuery 3 -->
  <script src="./bower_components/jquery/dist/jquery.min.js"></script>
  <!-- Bootstrap 3.3.7 -->
  <script src="./bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <!-- iCheck -->
  <script src="./plugins/iCheck/icheck.min.js"></script>
  <!-- javascript  -->
  <script src="./assets/js/register.js"></script>
  <script>
    $(function () {
      $('input').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' /* optional */
      });
    });
  </script>
</body>

</html>