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
                        <input type="hidden" name="userType" id="userType" value="user">
                    </div>
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="loginEmail" name="loginEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="loginPassword" name="loginPassword" required>
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
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <img id="img_logo" class="img-fluid " src="images/logo.png" alt="Logo">
        <a class="navbar-brand" href="index.php">TRIPSORUS</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item d-flex align-items-center">
                        <span class="nav-link fw-bold fst-italic">
                            <i class="bi bi-person-circle me-1 "></i>
                            <?php
                            if (isset($_SESSION['first_name'])) {
                                echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
                            } else {
                                echo 'Guest';
                            }
                            ?>
                        </span>

                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger fw-bold" href="logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                    </li>

                <?php else: ?>
                    <li class="nav-item fw-semibold">
                        <a class="nav-link" href="register-property-owner.php">
                            <i class="bi bi-house-add me-1"></i> List your Property
                        </a>
                    </li>
                    <li class="nav-item fw-semibold">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login/Externet Login
                        </a>
                    </li>
                    <li class="nav-item ms-2 fw-semibold">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#signupModal">
                            <i class="bi bi-person-plus me-1"></i> Sign Up
                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>