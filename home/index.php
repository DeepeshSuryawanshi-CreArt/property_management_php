<?php
require '../vendor/autoload.php';
require '../libs/database.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "my_key_kill";
$db = new database();
$all_set = true;

//  jwt working 
$jwt = $_COOKIE['token'];

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
    header('location:../login.html');
}

// get page from request (default = 1)
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;

$filter_type = isset($_GET['type']) && $_GET['type'] !== '' ? trim($_GET['type']) : null;
$filter_category = isset($_GET['category']) && $_GET['category'] !== '' ? trim($_GET['category']) : null;
$filter_name = isset($_GET['name']) && $_GET['name'] !== '' ? trim($_GET['name']) : null;

$response = $db->getallProperties($filter_type, $filter_category, $filter_name);

$properties = $response['data'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Property Details | Find Your Dream Home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body data-bs-spy="scroll" data-bs-target="#mainNavbar" data-bs-offset="80" tabindex="0">

    <?php include 'components/header.html' ?>
    <!-- Hero Section -->
    <section id="hero" class="hero-section position-relative d-flex align-items-center justify-content-center">
        <video class="bg-video" autoplay muted loop playsinline>
            <source src="https://assets.mixkit.co/videos/preview/mixkit-luxury-house-exterior-2995-large.mp4"
                type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="hero-overlay position-absolute w-100 h-100"></div>
        <div class="container position-relative text-center text-white hero-content">
            <h1 class="display-4 fw-bold mb-3">Find Your Dream Home Today</h1>
            <p class="lead mb-4">Discover the best properties for rent and sale in your city.</p>
            <a href="#properties" class="btn btn-lg btn-primary px-5 py-2 rounded-pill shadow explore-btn">Explore
                Properties</a>
        </div>
    </section>

    <!-- Search & Filter Section -->
    <!-- Properties Section with Filter -->
    <section id="properties" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-4">Featured Properties</h2>
            <!-- Filter Row -->
            <form id="property-filter" method="get" class="row g-3 mb-4 justify-content-center">
                <div class="col-md-3">
                    <select class="form-select" name="category" id="categoryFilter">
                        <option value="">Select Category</option>
                        <option value="apartment" <?php echo ($filter_category === 'apartment') ? 'selected' : '' ?>>Apartment</option>
                        <option value="penthouse" <?php echo ($filter_category === 'penthouse') ? 'selected' : '' ?>>Penthouse</option>
                        <option value="bungalow" <?php echo ($filter_category === 'bungalow') ? 'selected' : '' ?>>Bungalow</option>
                        <option value="residences" <?php echo ($filter_category === 'residences') ? 'selected' : '' ?>>Residences</option>
                        <option value="villa" <?php echo ($filter_category === 'villa') ? 'selected' : '' ?>>Villa</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="type" id="typeFilter">
                        <option value="">Select type</option>
                        <option value="rent" <?php echo ($filter_type === 'rent') ? 'selected' : '' ?>>Rent</option>
                        <option value="sale" <?php echo ($filter_type === 'sale') ? 'selected' : '' ?>>Sale</option>
                        <option value="other" <?php echo ($filter_type === 'other') ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="name" class="form-control" id="nameFilter" placeholder="Search by name..." value="<?php echo htmlspecialchars($filter_name ?? '') ?>">
                </div>
            </form>

            <div class="property-slider">
                <?php if (!empty($properties) && is_array($properties)): ?>
                    <?php foreach ($properties as $prop):
                        $id = htmlspecialchars($prop['id'] ?? '');
                        $name = htmlspecialchars($prop['name'] ?? 'Untitled');
                        $category = htmlspecialchars($prop['category'] ?? 'Unknown');
                        $type = htmlspecialchars(ucfirst($prop['type'] ?? ''));
                        $address = trim(htmlspecialchars(($prop['address'] ?? '') . ', ' . ($prop['city'] ?? '') . ', ' . ($prop['country'] ?? '') . ' ' . ($prop['zip_code'] ?? '')));
                        $photo = !empty($prop['photos']) ? htmlspecialchars($prop['photos']) : 'assets/img/default-property.jpg';
                        $price = isset($prop['price']) ? htmlspecialchars($prop['price']) : '';
                        ?>
                        <div class="card mx-2" data-category="<?php echo strtolower($category) ?>" data-type="<?php echo strtolower($prop['type'] ?? '') ?>"
                            data-name="<?php echo $name ?>">
                            <img src="../<?php echo $photo ?>" class="card-img-top" alt="<?php echo $name ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $name ?></h5>
                                <p class="mb-1"><span class="badge bg-primary"><?php echo $category ?></span> <span
                                        class="badge bg-success"><?php echo $type ?></span></p>
                                <p class="text-muted small mb-1"><i class="fa fa-map-marker-alt me-1"></i>
                                    <?php echo $address ?></p>
                                <?php if ($price !== ''): ?>
                                    <p class="fw-bold text-primary mb-0">$ <?php echo $price ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">No properties available.</p>
                <?php endif; ?>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mt-4">
                <?php if (!empty($properties) && is_array($properties)): ?>
                    <?php foreach ($properties as $prop):
                        $id = htmlspecialchars($prop['id'] ?? '');
                        $name = htmlspecialchars($prop['name'] ?? 'Untitled');
                        $category = htmlspecialchars($prop['category'] ?? 'Unknown');
                        $type = htmlspecialchars(ucfirst($prop['type'] ?? ''));
                        $address = trim(htmlspecialchars(($prop['address'] ?? '') . ', ' . ($prop['city'] ?? '') . ', ' . ($prop['country'] ?? '') . ' ' . ($prop['zip_code'] ?? '')));
                        $photo = !empty($prop['photos']) ? htmlspecialchars($prop['photos']) : 'assets/img/default-property.jpg';
                        $price = isset($prop['price']) ? htmlspecialchars($prop['price']) : '';
                        ?>
                        <div class="col">
                            <div class="card h-100 property-card" data-category="<?php echo strtolower($category) ?>" data-type="<?php echo strtolower($prop['type'] ?? '') ?>"
                                data-name="<?php echo $name ?>">
                                <img src="../<?php echo $photo ?>" class="card-img-top" alt="Modern Apartment">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $name ?></h5>
                                    <p class="text-muted small mb-1"><i class="fa fa-map-marker-alt me-1"></i>
                                        <?php echo $address ?></p>
                                    <p class="mb-1"><span class="badge bg-primary"><?php echo $category ?></span> <span
                                            class="badge bg-success"><?php echo $type ?></span></p>
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-bold text-primary mb-0">$ <?php echo $price ?></p>
                                        <a href="./details.php?id=<?php echo $id ?>" class=""> Details </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">No properties available.</p>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </section>

    <!-- Slick Carousel CSS & JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script>
        $(document).ready(function () {
            var $slider = $('.property-slider');
            $slider.slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                arrows: true,
                dots: true,
                responsive: [
                    { breakpoint: 1200, settings: { slidesToShow: 3 } },
                    { breakpoint: 992, settings: { slidesToShow: 2 } },
                    { breakpoint: 576, settings: { slidesToShow: 1 } }
                ]
            });

            // submit filters by GET when inputs change (debounced)
            var debounceTimeout = null;
            function submitFiltersDebounced() {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(function () {
                    $('#property-filter').submit();
                }, 350);
            }

            $('#categoryFilter, #typeFilter').on('change', submitFiltersDebounced);
            $('#nameFilter').on('input', submitFiltersDebounced);
        });
    </script>
    <style>
        .property-slider .card {
            min-width: 250px;
            border-radius: 1rem;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.07);
            overflow: hidden;
        }

        .property-slider .card-img-top {
            height: 180px;
            object-fit: cover;
        }

        .slick-dots li button:before {
            color: #0d6efd;
        }
    </style>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80"
                        alt="About" class="img-fluid rounded-4 shadow">
                </div>
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-3">About DreamEstate</h2>
                    <p class="mb-3">DreamEstate is your trusted partner in finding the perfect property. Whether you're
                        looking to buy, rent, or invest, our platform offers a curated selection of homes, apartments,
                        and villas to suit every lifestyle and budget.</p>
                    <ul class="list-unstyled">
                        <li><i class="fa fa-check text-primary me-2"></i> Verified Listings</li>
                        <li><i class="fa fa-check text-primary me-2"></i> Expert Guidance</li>
                        <li><i class="fa fa-check text-primary me-2"></i> Secure Transactions</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer-section py-5 text-white">
        <div class="container">
            <div class="row">
                <!-- Left: Logo + About -->
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="d-flex align-items-center mb-2">
                        <img src="https://img.icons8.com/ios-filled/50/ffffff/home.png" alt="Logo" width="32"
                            class="me-2">
                        <span class="fw-bold fs-5">DreamEstate</span>
                    </div>
                    <p class="small">Your trusted partner for finding, buying, and renting properties. Experience a
                        seamless journey to your dream home.</p>
                </div>
                <!-- Middle: Quick Links -->
                <div class="col-md-4 mb-4 mb-md-0">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="#hero" class="footer-link">Home</a></li>
                        <li><a href="#about" class="footer-link">About</a></li>
                        <li><a href="#properties" class="footer-link">Properties</a></li>
                        <li><a href="#contact" class="footer-link">Contact</a></li>
                    </ul>
                </div>
                <!-- Right: Contact + Social -->
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

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="assets/js/index.js"></script>
    <script src="assets/js/auth.js"></script>
</body>

</html>