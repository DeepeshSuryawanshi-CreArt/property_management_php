<?php
require '../vendor/autoload.php';
require '../libs/database.php';

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
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Property Details — DreamEstate</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/details.css">
  <meta name="description" content="Property details — modern layout with gallery, features and related properties">
</head>

<body>

  <!-- Header / Navbar -->
  <?php include 'components/header.html' ?>
  <main class="container" id="details">
    <!-- Property Details Section -->
    <section class="property-details my-5">
      <div class="row g-4 align-items-start">
        <div class="col-lg-7">
          <div class="property-media shadow-sm rounded-3 overflow-hidden">
            <img src="../<?php echo htmlspecialchars($property['photos'])?>"
              alt="Modern Luxury Villa" class="img-fluid main-photo">
          </div>
        </div>
        <div class="col-lg-5">
          <div class="card details-card shadow-sm p-4">
            <h1 class="h4 fw-bold mb-2"><?php echo htmlspecialchars($property['name'])?></h1>
            <div class="mb-3">
              <span class="badge bg-primary me-2"><?php echo htmlspecialchars($property['category'])?></span>
              <span class="badge bg-success"><?php echo htmlspecialchars($property['type'])?></span>
            </div>
            <p class="text-muted mb-2"><i class="fa fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($property['address'])?>,<?php echo htmlspecialchars($property['city'])?> (<?php echo htmlspecialchars($property['country'])?>)</p>
            <p class="h4 text-primary fw-bold mb-3">$ <?php echo htmlspecialchars($property['price'])?></p>

            <ul class="list-unstyled mb-3 details-list">
              <li><strong>Bedrooms:</strong> 5</li>
              <li><strong>Bathrooms:</strong> 4</li>
              <li><strong>Area:</strong> 4,200 sq ft</li>
              <li><strong>Year Built:</strong> 2021</li>
            </ul>

            <p class="mb-4 text-muted">Experience luxurious living in this modern villa with panoramic views, open-plan
              living areas, and designer finishes. Located in a prestigious neighborhood close to amenities.</p>

            <div class="d-flex gap-2">
              <a href="#contact" class="btn btn-primary btn-lg flex-grow-1">Contact Agent</a>
              <a href="#" class="btn btn-outline-secondary btn-lg">Save</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Related Properties -->
    <section id="related" class="related-properties my-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h5 mb-0">Related Properties</h2>
        <a href="/" class="small text-muted">View all</a>
      </div>

      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
        <div class="col">
          <div class="card property-card h-100">
            <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?auto=format&fit=crop&w=800&q=80"
              class="card-img-top" alt="Property 1">
            <div class="card-body">
              <h5 class="card-title">Modern Apartment</h5>
              <p class="mb-1 text-primary fw-bold">$1,200 / mo</p>
              <p class="text-muted small"><i class="fa fa-map-marker-alt me-1"></i> New York, NY</p>
              <a href="#" class="btn btn-outline-primary mt-3">View Details</a>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card property-card h-100">
            <img src="https://images.unsplash.com/photo-1460518451285-97b6aa326961?auto=format&fit=crop&w=800&q=80"
              class="card-img-top" alt="Property 2">
            <div class="card-body">
              <h5 class="card-title">Cozy Bungalow</h5>
              <p class="mb-1 text-primary fw-bold">$850,000</p>
              <p class="text-muted small"><i class="fa fa-map-marker-alt me-1"></i> Miami, FL</p>
              <a href="#" class="btn btn-outline-primary mt-3">View Details</a>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card property-card h-100">
            <img src="https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=800&q=80"
              class="card-img-top" alt="Property 3">
            <div class="card-body">
              <h5 class="card-title">Urban Apartment</h5>
              <p class="mb-1 text-primary fw-bold">$1,500 / mo</p>
              <p class="text-muted small"><i class="fa fa-map-marker-alt me-1"></i> San Francisco, CA</p>
              <a href="#" class="btn btn-outline-primary mt-3">View Details</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer id="contact" class="footer-section py-5 text-white">
    <div class="container">
      <div class="row">
        <div class="col-md-4 mb-4 mb-md-0">
          <div class="d-flex align-items-center mb-2">
            <img src="https://img.icons8.com/ios-filled/50/ffffff/home.png" alt="Logo" width="32" class="me-2">
            <span class="fw-bold fs-5">DreamEstate</span>
          </div>
          <p class="small">Your trusted partner for finding, buying, and renting properties. Experience a seamless
            journey to your dream home.</p>
        </div>
        <div class="col-md-4 mb-4 mb-md-0">
          <h6 class="fw-bold mb-3">Quick Links</h6>
          <ul class="list-unstyled">
            <li><a href="#details" class="footer-link">Home</a></li>
            <li><a href="#about" class="footer-link">About</a></li>
            <li><a href="#details" class="footer-link">Properties</a></li>
            <li><a href="#contact" class="footer-link">Contact</a></li>
          </ul>
        </div>
        <div class="col-md-4">
          <h6 class="fw-bold mb-3">Contact Us</h6>
          <p class="mb-1"><i class="fa fa-envelope me-2"></i> info@dreamestate.com</p>
          <p class="mb-3"><i class="fa fa-phone me-2"></i> +1 234 567 890</p>
          <div class="d-flex gap-3">
            <a href="#" class="footer-social"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="footer-social"><i class="fab fa-instagram"></i></a>
            <a href="#" class="footer-social"><i class="fab fa-whatsapp"></i></a>
            <a href="#" class="footer-social"><i class="fab fa-x-twitter"></i></a>
          </div>
        </div>
      </div>
      <hr class="border-light my-4">
      <div class="text-center small">&copy; 2025 DreamEstate. All rights reserved.</div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
  <script src="assets/js/details.js"></script>
  <script src="assets/js/auth.js"></script>
</body>

</html>