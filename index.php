<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRIPSORUS</title>
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
                    <form method="POST" action="login.php" id="loginForm">
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
                            <input type="hidden" name="userType" id="userType" value="user">
                        </div>

                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="loginEmail" name="loginEmail" required>
                        </div>

                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="loginPassword" name="loginPassword"
                                required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
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
                    <form id="signupForm" method="POST" action="signup.php">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="signupEmail" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="signupEmail" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>

                        <div class="mb-3">
                            <label for="signupPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="signupPassword" name="password" required>
                        </div>

                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password"
                                required>
                        </div>

                        <input type="hidden" name="user_type" value="user">

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="termsAgree" name="termsAgree" required>
                            <label class="form-check-label" for="termsAgree">
                                I agree to the <a href="#">Terms & Conditions</a>
                            </label>
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

                    <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Show name with icon -->
                    <li class="nav-item d-flex align-items-center">
                        <span class="nav-link">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php 
                                   if (isset($_SESSION['first_name']) ) {
                                   echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
                                   } 
                                   else {
                                  echo 'Guest';
                                       }
                            ?>
                        </span>

                    </li>

                    <!-- Logout option -->
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                    </li>

                    <?php else: ?>
                    <!-- For not logged-in users: Show login/signup -->
                    <li class="nav-item">
                        <a class="nav-link" href="register-property-owner.php">
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
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>


    <!-- Main content box -->
    <div class="container my-4">
        <div class="content-box">
            <!-- Search and Top Hotels in one row -->
            <div class="row">
                <!-- Search section -->
                <div class="col-lg-4">
                    <div class="search-section">
                        <h5 class="mb-3">Search Hotels, Location, Landmark</h5>
                        <form action="searchHotels.php" method="GET">
                            <div class="mb-3">
                                <input type="text" name="location" class="form-control"
                                    placeholder="Where are you going?" required>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 mb-2">
                                    <input type="date" name="checkin" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="date" name="checkout" class="form-control" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="rooms">Rooms</label>
                                    <select id="rooms" name="rooms" class="form-control">
                                        <option value="1">1 Room</option>
                                        <option value="2">2 Rooms</option>
                                        <option value="3">3 Rooms</option>
                                        <option value="4">4 Rooms</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="adults">Adults</label>
                                    <select id="adults" name="adults" class="form-control">
                                        <option value="1">1 Adult</option>
                                        <option value="2" selected>2 Adults</option>
                                        <option value="3">3 Adults</option>
                                        <option value="4">4 Adults</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="search-btn-main">Search Hotels</button>
                        </form>
                    </div>
                </div>

                <!-- Top Hotels section -->
                <div class="col-lg-8 mt-3 mt-lg-0">
                    <h5 class="section-title-boxed">Top Hotels</h5>
                    <div class="row">
                        <!-- Hotel 1 -->
                        <div class="col-md-6 mb-4">
                            <div class="hotel-card-compact">
                                <img src="images/img1.jpg" class="hotel-img-compact" alt="Riverside Inn">
                                <div class="p-3">
                                    <h6 class="mb-1">Riverside Inn</h6>
                                    <p class="text-muted small mb-2">Jamshedpur</p>
                                    <div class="d-flex align-items-center">
                                        <span class="text-warning small">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </span>
                                        <span class="text-muted small ms-2">235 reviews</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hotel 2 -->
                        <div class="col-md-6 mb-4">
                            <div class="hotel-card-compact">
                                <img src="images/img1.jpg" class="hotel-img-compact" alt="The Lindsay">
                                <div class="p-3">
                                    <h6 class="mb-1">The Lindsay</h6>
                                    <p class="text-muted small mb-2">Kolkata</p>
                                    <div class="d-flex align-items-center">
                                        <span class="text-warning small">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="far fa-star"></i>
                                        </span>
                                        <span class="text-muted small ms-2">235 reviews</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hotel 3 -->
                        <div class="col-md-6 mb-4">
                            <div class="hotel-card-compact">
                                <img src="images/img1.jpg" class="hotel-img-compact" alt="Green Acre">
                                <div class="p-3">
                                    <h6 class="mb-1">Green Acre</h6>
                                    <p class="text-muted small mb-2">Ranchi</p>
                                    <div class="d-flex align-items-center">
                                        <span class="text-warning small">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </span>
                                        <span class="text-muted small ms-2">235 reviews</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hotel 4 -->
                        <div class="col-md-6 mb-4">
                            <div class="hotel-card-compact">
                                <img src="images/img1.jpg" class="hotel-img-compact" alt="The Sonnet">
                                <div class="p-3">
                                    <h6 class="mb-1">The Sonnet</h6>
                                    <p class="text-muted small mb-2">Jamshedpur</p>
                                    <div class="d-flex align-items-center">
                                        <span class="text-warning small">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </span>
                                        <span class="text-muted small ms-2">235 reviews</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Official Hotels -->
            <div class="mt-5">
                <h5 class="section-title-boxed">Official</h5>
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="hotel-card-compact">
                            <img src="images/img2.jpg" class="hotel-img-compact" alt="The Sonnet">
                            <div class="p-3">
                                <h6 class="mb-1">The Sonnet</h6>
                                <p class="text-muted small mb-2">Jamshedpur</p>
                                <div class="d-flex align-items-center">
                                    <span class="text-warning small">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </span>
                                    <span class="text-muted small ms-2">235 reviews</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Popular Destinations -->
            <div class="mt-5">
                <h5 class="section-title-boxed">Popular destinations</h5>
                <div class="row">
                    <div class="col-6 col-md-4 col-lg-2 mb-3">
                        <div class="destination-card-compact">
                            <img src="images/img1.jpg" class="destination-img-compact" alt="Manali">
                            <div class="destination-name-compact">Manali</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 mb-3">
                        <div class="destination-card-compact">
                            <img src="images/img1.jpg" class="destination-img-compact" alt="Varanasi">
                            <div class="destination-name-compact">Varanasi</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 mb-3">
                        <div class="destination-card-compact">
                            <img src="images/img1.jpg" class="destination-img-compact" alt="Agra">
                            <div class="destination-name-compact">Agra</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 mb-3">
                        <div class="destination-card-compact">
                            <img src="images/img1.jpg" class="destination-img-compact" alt="Goa">
                            <div class="destination-name-compact">Goa</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2 mb-3">
                        <div class="destination-card-compact">
                            <img src="images/img1.jpg" class="destination-img-compact" alt="Kolkata">
                            <div class="destination-name-compact">Kolkata</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deals for the weekend -->
            <div class="mt-5">
                <h5 class="section-title-boxed">Deals for the weekend</h5>
                <div class="row">
                    <!-- Deal 1 -->
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="hotel-card-compact deal-card">
                            <img src="images/img1.jpg" class="card-img-top" alt="Radisson Blu">
                            <div class="p-3">
                                <h6 class="mb-1">Radisson Blu</h6>
                                <p class="text-muted small mb-2">Ranchi, Jharkhand</p>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning small">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </span>
                                    <span class="text-muted small ms-2">235 reviews</span>
                                </div>
                                <div>
                                    <span class="price-old small">Rs. 3500</span>
                                    <span class="price-new">Rs. 2150 / Night</span>
                                </div>
                                <button class="btn btn-primary btn-sm w-100 mt-2">Book Now</button>
                            </div>
                        </div>
                    </div>

                    <!-- Deal 2 -->
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="hotel-card-compact deal-card">
                            <img src="images/img2.jpg" class="card-img-top" alt="Radisson Blu">
                            <div class="p-3">
                                <h6 class="mb-1">Radisson Blu</h6>
                                <p class="text-muted small mb-2">Ranchi, Jharkhand</p>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning small">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </span>
                                    <span class="text-muted small ms-2">198 reviews</span>
                                </div>
                                <div>
                                    <span class="price-old small">Rs. 3500</span>
                                    <span class="price-new">Rs. 2150 / Night</span>
                                </div>
                                <button class="btn btn-primary btn-sm w-100 mt-2">Book Now</button>
                            </div>
                        </div>
                    </div>

                    <!-- Deal 3 -->
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="hotel-card-compact deal-card">
                            <img src="images/img1.jpg" class="card-img-top" alt="Radisson Blu">
                            <div class="p-3">
                                <h6 class="mb-1">Radisson Blu</h6>
                                <p class="text-muted small mb-2">Ranchi, Jharkhand</p>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning small">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </span>
                                    <span class="text-muted small ms-2">312 reviews</span>
                                </div>
                                <div>
                                    <span class="price-old small">Rs. 3500</span>
                                    <span class="price-new">Rs. 2150 / Night</span>
                                </div>
                                <button class="btn btn-primary btn-sm w-100 mt-2">Book Now</button>
                            </div>
                        </div>
                    </div>

                    <!-- Deal 4 -->
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="hotel-card-compact deal-card">
                            <img src="images/img1.jpg" class="card-img-top" alt="Radisson Blu">
                            <div class="p-3">
                                <h6 class="mb-1">Radisson Blu</h6>
                                <p class="text-muted small mb-2">Ranchi, Jharkhand</p>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="text-warning small">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </span>
                                    <span class="text-muted small ms-2">276 reviews</span>
                                </div>
                                <div>
                                    <span class="price-old small">Rs. 3500</span>
                                    <span class="price-new">Rs. 2150 / Night</span>
                                </div>
                                <button class="btn btn-primary btn-sm w-100 mt-2">Book Now</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner Section -->
            <div class="mt-5 text-center bg-light p-4 rounded">
                <h5>Affordable places to stay in India!</h5>
                <p class="mb-0">TRIPSORUS</p>
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
    <!-- At the bottom of your HTML page, just before </body> -->
    <script>
        const buttons = document.querySelectorAll('.user-type-btn');
        const userTypeInput = document.getElementById('userType');

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                userTypeInput.value = btn.getAttribute('data-user-type');
            });
        });
    </script>
</body>

</html>

</body>

</html>