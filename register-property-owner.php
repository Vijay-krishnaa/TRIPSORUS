<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: admin-panel.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register as Property Owner</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .card {
      margin-top: 20px;
      padding: 20px;
    }

    .btn {
      margin-top: 10px;
    }
  </style>
</head>

<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8 text-center">
        <h2 class="mb-4">Register as Property Owner</h2>
        <p class="lead">To list your property, you need to register as a property owner/admin.</p>
      </div>
    </div>

    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card">
          <div class="card-body text-center">
            <h5 class="card-title">New Owner?</h5>
            <p>Create a new property owner account</p>
            <a href="register.php?user_type=admin" class="btn btn-primary btn-lg">
              <i class="fas fa-user-plus me-2"></i>Register Now
            </a>
          </div>
        </div>
      </div>


    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>