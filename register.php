<?php
session_start();
require_once 'db.php';

$user_type = $_GET['user_type'] ?? 'user';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type  = $_POST['user_type'] ?? 'user';

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('All fields are required'); window.history.back();</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format'); window.history.back();</script>";
        exit;
    }

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match'); window.history.back();</script>";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO user (first_name, last_name, email, password, user_type, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $email, $hashed_password, $user_type, $phone]);

      
        echo "<script>
            alert('Signup successful! Please log in with your credentials.');
            window.location.href = 'index.php';
        </script>";
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "<script>alert('Duplicate email detected. Please use a different email.'); window.history.back();</script>";
        } else {
            echo "<script>alert('Registration failed: " . $e->getMessage() . "'); window.history.back();</script>";
        }
        exit;
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Property Owner Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="styles/style.css" />
</head>

<body>
  <div class="container-fluid">
    <div class="registration-container">
      <div class="registration-header">
        <h2><i class="fas fa-hotel me-2"></i> Property Owner Registration</h2>
        <p class="mb-0">Register to list your property on our platform</p>
      </div>

      <div class="registration-form">
        <form method="POST">
          <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($user_type); ?>">

          <?php if ($user_type === 'admin'): ?>
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> You're registering as a Property Owner/Admin
          </div>
          <?php endif; ?>

          <!-- Personal Information Section -->
          <div class="form-section">
            <h5><i class="fas fa-user-circle me-2"></i> Personal Information</h5>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label required-field">First Name</label>
                <input type="text" name="first_name" class="form-control" required />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label required-field">Last Name</label>
                <input type="text" name="last_name" class="form-control" required />
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label required-field">Email</label>
                <input type="email" name="email" class="form-control" required />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label required-field">Phone Number</label>
                <input type="tel" name="phone" class="form-control" required />
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label required-field">Password</label>
                <div class="input-group">
                  <input type="password" name="password" class="form-control" required />
                  <button class="btn btn-outline-secondary toggle-password" type="button">
                    <i class="fas fa-eye"></i>
                  </button>
                </div>
                <small class="text-muted">Minimum 8 characters</small>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label required-field">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required />
              </div>
            </div>


            <!-- Terms and Submit -->
            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" id="terms" required />
              <label class="form-check-label" for="terms">
                I agree to the
                <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
              </label>
            </div>

            <div class="text-center">
              <button type="submit" class="btn btn-primary btn-lg btn-register">
                <i class="fas fa-user-plus me-2"></i> Complete Registration
              </button>
            </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Terms Modal -->
  <div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Terms and Conditions</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Terms content here -->
        </div>
      </div>
    </div>
  </div>
</body>

</html>