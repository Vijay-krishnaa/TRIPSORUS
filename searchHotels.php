<?php
// Top of the file, before any HTML output
include 'db.php';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 4;
$offset = ($page - 1) * $limit;

$location = $_GET['location'] ?? '';
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$rooms = $_GET['rooms'] ?? '';
$adults = $_GET['adults'] ?? '';

$sql = "SELECT * FROM hotels WHERE location LIKE ? LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%$location%";
$stmt->bind_param("sii", $searchTerm, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM hotels WHERE location LIKE ?");
$countStmt->bind_param("s", $searchTerm);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRow = $countResult->fetch_assoc();
$totalHotels = $totalRow['total'];
$totalPages = ceil($totalHotels / $limit);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotels in Kolkata - TRIPSORUS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <link rel="icon" href="images/favicon.ico" type="image/png">
    <link rel="stylesheet" href="styles/style.css">
</head>

<body>
    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Login to TRIPSORUS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Login as</label>
                            <div class="d-flex justify-content-between mb-3">
                                <button type="button" class="btn btn-outline-primary user-type-btn active"
                                    data-user-type="user">
                                    <i class="bi bi-person me-1"></i> User
                                </button>
                                <button type="button" class="btn btn-outline-primary user-type-btn"
                                    data-user-type="admin">
                                    <i class="bi bi-shield-lock me-1"></i> Admin
                                </button>
                                <button type="button" class="btn btn-outline-primary user-type-btn"
                                    data-user-type="superadmin">
                                    <i class="bi bi-star-fill me-1"></i> Super Admin
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="loginEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="loginPassword" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="#" class="text-decoration-none">Forgot password?</a>
                    </div>
                    <hr>
                    <div class="text-center">
                        <p class="mb-0">Don't have an account? <a href="#" class="text-primary" data-bs-toggle="modal"
                                data-bs-target="#signupModal" data-bs-dismiss="modal">Sign up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signup Modal remains the same as before -->
    <div class="modal fade" id="signupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Your Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="signupEmail" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="signupEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="signupPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="signupPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="termsAgree" required>
                            <label class="form-check-label" for="termsAgree">I agree to the <a href="#">Terms &
                                    Conditions</a></label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Create Account</button>
                    </form>
                    <hr>
                    <div class="text-center">
                        <p class="mb-0">Already have an account? <a href="#" class="text-primary" data-bs-toggle="modal"
                                data-bs-target="#loginModal" data-bs-dismiss="modal">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <img id="img_logo" class="img-fluid" src="images/logo.png" alt="Logo">
            <a class="navbar-brand" href="#">TRIPSORUS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-house-add me-1"></i> List your Property
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#signupModal">
                            <i class="bi bi-person-plus me-1"></i> Sign Up
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Add this script to handle the user type selection -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const userTypeBtns = document.querySelectorAll('.user-type-btn');

            userTypeBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    // Remove active class from all buttons
                    userTypeBtns.forEach(b => b.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');
                    // You can also store the selected user type in a hidden field if needed
                });
            });
        });
    </script>

    <!-- Search Header -->
    <section class="search-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-2">Search Hotels, Location, Landmark</h5>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-light text-dark me-2">1 Rooms, 2 Adults</span>
                        <span class="badge bg-light text-dark">Thu, Mar 20 - Fri, Mar 21</span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <button class="btn btn-light">Modify Search</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-4">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3">
                <div class="filter-sidebar">
                    <h4 class="mb-3">Filter by:</h4>

                    <!-- Budget Filter -->
                    <div class="filter-section">
                        <h6 class="filter-title">Your budget (per night)</h6>
                        <div class="range-slider mb-2">
                            <input type="range" class="form-range" min="400" max="6000" step="100" id="budgetRange">
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>₹ 400</span>
                            <span>₹ 6,000+</span>
                        </div>
                    </div>

                    <!-- Star Rating -->
                    <div class="filter-section">
                        <h6 class="filter-title">Star Rating</h6>
                        <div class="filter-option form-check">
                            <input class="form-check-input" type="checkbox" id="rating5">
                            <label class="form-check-label" for="rating5">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </label>
                        </div>
                        <div class="filter-option form-check">
                            <input class="form-check-input" type="checkbox" id="rating4">
                            <label class="form-check-label" for="rating4">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="far fa-star text-warning"></i>
                            </label>
                        </div>
                        <div class="filter-option form-check">
                            <input class="form-check-input" type="checkbox" id="rating3">
                            <label class="form-check-label" for="rating3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="far fa-star text-warning"></i>
                                <i class="far fa-star text-warning"></i>
                            </label>
                        </div>
                    </div>

                    <!-- Bed Size -->
                    <div class="filter-section">
                        <h6 class="filter-title">Bed Size</h6>
                        <div class="filter-option form-check">
                            <input class="form-check-input" type="checkbox" id="bedQueen">
                            <label class="form-check-label" for="bedQueen">Queen Bed</label>
                        </div>
                        <div class="filter-option form-check">
                            <input class="form-check-input" type="checkbox" id="bedKing">
                            <label class="form-check-label" for="bedKing">King Bed</label>
                        </div>
                        <div class="filter-option form-check">
                            <input class="form-check-input" type="checkbox" id="bedDouble">
                            <label class="form-check-label" for="bedDouble">Double Bed</label>
                        </div>
                        <div class="filter-option form-check">
                            <input class="form-check-input" type="checkbox" id="bedTwin">
                            <label class="form-check-label" for="bedTwin">Twin Beds</label>
                        </div>
                    </div>

                    <!-- Popular Filters -->
                    <div class="filter-section">
                        <h6 class="filter-title">Popular Filters</h6>
                        <div class="filter-option form-check">
                            <input class="form-check-input" type="checkbox" id="breakfast">
                            <label class="form-check-label" for="breakfast">Breakfast included <span
                                    class="text-muted">(359)</span></label>
                        </div>
                        <div class="filter-option form-check">
                            <input class="form-check-input" type="checkbox" id="freeCancel">
                            <label class="form-check-label" for="freeCancel">Free cancellation <span
                                    class="text-muted">(356)</span></label>
                        </div>
                        <div class="filter-option form-check">
                            <input class="form-check-input" type="checkbox" id="aircon">
                            <label class="form-check-label" for="aircon">Air conditioning <span
                                    class="text-muted">(157)</span></label>
                        </div>
                        <div class="filter-option form-check">
                            <input class="form-check-input" type="checkbox" id="balcony">
                            <label class="form-check-label" for="balcony">Balcony <span
                                    class="text-muted">(21)</span></label>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 mt-2">Apply Filters</button>
                    <button class="btn btn-outline-secondary w-100 mt-2">Reset All</button>
                </div>
            </div>

            <!-- Results Column -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">
                        <?php echo htmlspecialchars($location); ?>:
                        <?php echo $totalHotels; ?> properties found

                    </h4>


                    <div class="sort-dropdown">
                        <select class="form-select">
                            <option selected>Sort by: Our top picks</option>
                            <option>Price (low to high)</option>
                            <option>Price (high to low)</option>
                            <option>Star Rating</option>
                            <option>Guest Rating</option>
                            <option>Distance from center</option>
                        </select>
                    </div>
                </div>

                <!-- Hotel 1 -->
                <?php if ($result->num_rows > 0): ?>

                <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card hotel-card mb-4">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <img src="<?php echo htmlspecialchars($row['image'] ?? 'images/img1.jpg'); ?>"
                                class="img-fluid hotel-img" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        </div>
                        <div class="col-md-5">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </h5>
                                <p class="card-text text-muted small">
                                    <?php echo htmlspecialchars($row['location']); ?>
                                </p>
                                <div class="mb-2">
                                    <span class="badge bg-light text-dark me-1">Guest room, 1 Queen</span>
                                    <!-- Customize as needed -->
                                </div>
                                <p class="card-text"><i class="fas fa-bed me-2"></i> 1 queen bed</p>
                                <!-- Customize based on data -->
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-success"><i class="fas fa-utensils me-1"></i> Breakfast
                                        included</span>
                                    <span class="badge bg-success"><i class="fas fa-times-circle me-1"></i> Free
                                        cancellation</span>
                                    <span class="badge bg-success"><i class="fas fa-money-bill-wave me-1"></i> Pay at
                                        property</span>
                                </div>
                                <p class="text-danger small"><i class="fas fa-exclamation-circle me-1"></i> Only 5 rooms
                                    left at this price on our site</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card-body h-100 d-flex flex-column justify-content-between">
                                <div class="text-end">
                                    <div class="hotel-rating mb-2">
                                        <span class="badge bg-success">Excellent 8.7</span>
                                        <!-- You can output actual rating if available -->
                                        <p class="small mb-0">1,858 reviews</p> <!-- Customize -->
                                    </div>
                                    <p class="small mb-1">
                                        <?php echo htmlspecialchars($checkin) . " to " . htmlspecialchars($checkout) . ": " . htmlspecialchars($adults) . " adults, " . htmlspecialchars($rooms) . " room(s)"; ?>
                                    </p>
                                    <h4 class="price-new">₹
                                        <?php echo number_format($row['price']); ?>
                                    </h4>
                                    <p class="small text-muted">+ taxes and fees</p>
                                </div>
                                <button class="btn btn-primary w-100">See availability</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>

                <?php else: ?>
                <p>No hotels found for "
                    <?php echo htmlspecialchars($location); ?>".
                </p>
                <?php endif; ?>

                <!-- Pagination -->
                <nav aria-label="Search results pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Previous button -->
                        <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                            <a class="page-link"
                                href="?location=<?php echo urlencode($location); ?>&checkin=<?php echo $checkin; ?>&checkout=<?php echo $checkout; ?>&rooms=<?php echo $rooms; ?>&adults=<?php echo $adults; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                        </li>

                        <!-- Page numbers -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                            <a class="page-link"
                                href="?location=<?php echo urlencode($location); ?>&checkin=<?php echo $checkin; ?>&checkout=<?php echo $checkout; ?>&rooms=<?php echo $rooms; ?>&adults=<?php echo $adults; ?>&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <!-- Next button -->
                        <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                            <a class="page-link"
                                href="?location=<?php echo urlencode($location); ?>&checkin=<?php echo $checkin; ?>&checkout=<?php echo $checkout; ?>&rooms=<?php echo $rooms; ?>&adults=<?php echo $adults; ?>&page=<?php echo $page + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>

            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-container">
                <div class="footer-column">
                    <h3>Booking.com</h3>
                    <ul>
                        <li><a href="#">About Booking.com</a></li>
                        <li><a href="#">Customer Service Help</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Sustainability</a></li>
                        <li><a href="#">Press Center</a></li>
                        <li><a href="#">Safety Resource Center</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Terms &amp; Conditions</h3>
                    <ul>
                        <li><a href="#">Terms of use</a></li>
                        <li><a href="#">Privacy &amp; Cookies</a></li>
                        <li><a href="#">Privacy &amp; Cookies</a></li>
                        <li><a href="#">Copyright Dispute Policy</a></li>
                        <li><a href="#">How We Work</a></li>
                        <li><a href="#">Corporate contact</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Explore</h3>
                    <ul>
                        <li><a href="#">Unique places to stay</a></li>
                        <li><a href="#">Reviews</a></li>
                        <li><a href="#">Travel Communities</a></li>
                        <li><a href="#">Seasonal deals</a></li>
                        <li><a href="#">Travel articles</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Get the app</h3>
                    <ul>
                        <li><a href="#"><i class="fab fa-apple"></i> iOS app</a></li>
                        <li><a href="#"><i class="fab fa-android"></i> Android app</a></li>
                    </ul>
                    <h3 style="margin-top: 20px;">Connect with us</h3>
                    <div style="display: flex; gap: 15px; margin-top: 10px;">
                        <a href="#" style="font-size: 18px;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" style="font-size: 18px;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="font-size: 18px;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="font-size: 18px;"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="copyright">
                <p>Copyright © 2025 The Golden Sand - Luxury Beach Resort. All rights reserved.</p>
                <p>This is a demo website for educational purposes only, not affiliated with Booking.com</p>
            </div>
        </div>
    </footer>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>