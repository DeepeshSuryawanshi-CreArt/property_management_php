<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Property Dashboard</title>
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <!-- AdminLTE & Bootstrap -->
  <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'components/header.php'; ?>
  <?php include 'components/sidbar.php'; ?>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <!-- Page header -->
    <section class="content-header">
      <h1>Property Dashboard</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Property Table</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="box">
        <div class="box-header with-border d-flex justify-content-between">
          <h3 class="box-title">Property List</h3>
          <a href="add_property.php" class="btn btn-success btn-sm">
            <i class="fa fa-plus"></i> Add Property
          </a>
        </div>
        <div class="box-body">
          <table id="propertyTable" class="table table-bordered table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Property Name</th>
                <th>Category</th>
                <th>Type</th>
                <th>City</th>
                <th>Zip</th>
                <th>Listed By</th>
                <th>Photos</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- JS will load properties here -->
            </tbody>
            <div id="pagination" class="mt-3"></div>
          </table>
        </div>
      </div>
    </section>
  </div>

  <?php include 'components/footer.php'; ?>
  <div class="control-sidebar-bg"></div>
</div>

<!-- JS -->
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
<script src="assets/js/dashboard.js"></script>
</body>
</html>