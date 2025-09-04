<?php
require_once '../db.php';
$stmt = $pdo->query("
    SELECT *
    FROM bookings");
$bookings = $stmt->fetchAll();
$limit = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$totalStmt = $pdo->query("SELECT COUNT(*) FROM bookings");
$totalBookings = $totalStmt->fetchColumn();
$totalPages = ceil($totalBookings / $limit);
$stmt = $pdo->prepare("SELECT * FROM bookings ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bookings - TRIPSORUS Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles/style.css">
</head>
<body>
  <!-- Sidebar Navigation -->
  <?php include 'sidebar.php'; ?>
  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Bookings</h2>
        <div class="d-flex">
          <button class="btn btn-primary me-3">
            <i class="fas fa-plus me-2"></i> New Booking
          </button>
          <button class="btn btn-outline-secondary">
            <i class="fas fa-download me-2"></i> Export
          </button>
        </div>
      </div>

      <!-- Booking Filters -->
      <div class="card mb-4 p-3">
        <div class="row">
          <div class="col-md-3 mb-2">
            <label for="bookingStatusFilter" class="form-label">Booking Status</label>
            <select class="form-select" id="bookingStatusFilter">
              <option value="">All Statuses</option>
              <option value="confirmed">Confirmed</option>
              <option value="pending">Pending</option>
              <option value="cancelled">Cancelled</option>
              <option value="completed">Completed</option>
            </select>
          </div>
          <div class="col-md-3 mb-2">
            <label for="propertyFilter" class="form-label">Property</label>
            <select class="form-select" id="propertyFilter">
              <option value="">All Properties</option>
              <option value="grand-plaza">Grand Plaza Hotel</option>
              <option value="beach-view">Beach View Resort</option>
              <option value="mountain-villa">Mountain Villa</option>
            </select>
          </div>
          <div class="col-md-3 mb-2">
            <label for="dateRangeFilter" class="form-label">Date Range</label>
            <input type="text" class="form-control" id="dateRangeFilter" placeholder="Select date range">
          </div>
          <div class="col-md-3 mb-2 d-flex align-items-end">
            <button class="btn btn-primary w-100">
              <i class="fas fa-filter me-2"></i> Apply Filters
            </button>
          </div>
        </div>
      </div>
      <!-- Bookings Table -->
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">All Bookings</h5>
          <div class="input-group" style="width: 250px;">
            <input type="text" class="form-control" placeholder="Search bookings...">
            <button class="btn btn-outline-secondary" type="button">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Booking ID</th>
                  <th>Property</th>
                  <th>First Name</th>
                  <th>Last Name</th>
                  <th>Check-In</th>
                  <th>Check-Out</th>
                  <th>Amount</th>
                  <th>Meal Type</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($bookings) > 0): ?>
                  <?php foreach ($bookings as $booking): ?>
                  <tr>
                    <td>#<?= htmlspecialchars($booking['booking_code']); ?></td>
                    <td><?= htmlspecialchars($booking['property_name']); ?></td>
                    <td><?= htmlspecialchars($booking['first_name']); ?></td>
                    <td><?= htmlspecialchars($booking['last_name']); ?></td>
                    <td><?= date("d M Y", strtotime($booking['check_in'])); ?></td>
                    <td><?= date("d M Y", strtotime($booking['check_out'])); ?></td>
                    <td><i class="fas fa-coins me-1"></i> ₹<?= number_format($booking['amount']); ?></td>
                    <td>
                      <?php
                        $mealTypeClass = match($booking['meal_type']) {
                          'breakfast' => 'bg-info',
                          'half-board' => 'bg-primary',
                          'full-board' => 'bg-success',
                          'all-inclusive' => 'bg-warning text-dark',
                          default => 'bg-info'
                        };
                      ?>
                      <span class="badge <?php echo $mealTypeClass; ?>"><?php echo ucfirst(str_replace('-', ' ', $booking['meal_type'])); ?></span>
                    </td>
                    <td>
                      <?php
                        $statusClass = match($booking['status']) {
                          'Confirmed' => 'bg-success',
                          'Pending'   => 'bg-warning text-dark',
                          'Cancelled' => 'bg-danger',
                          'Completed' => 'bg-info',
                          default     => ''
                        };
                      ?>
                      <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($booking['status']); ?></span>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="tooltip" title="View Details"><i class="fas fa-eye"></i></button>
                      <?php if ($booking['status'] === 'confirmed'): ?>
                        <button class="btn btn-sm btn-outline-success me-1" data-bs-toggle="tooltip" title="Check-In"><i class="fas fa-door-open"></i></button>
                      <?php elseif ($booking['status'] === 'pending'): ?>
                        <button class="btn btn-sm btn-outline-success me-1" data-bs-toggle="tooltip" title="Confirm"><i class="fas fa-check"></i></button>
                      <?php endif; ?>
                      <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Cancel"><i class="fas fa-times"></i></button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr><td colspan="10" class="text-center">No bookings found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <!-- Pagination -->
          <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $page - 1; ?>">Previous</a>
            </li>
        <?php endif; ?>
&emsp;
        <li class="page-item">
            <form method="get" class="d-inline">
                <input type="number" name="page" value="<?= $page; ?>" min="1" max="<?= $totalPages; ?>" 
                       onchange="this.form.submit()" 
                       class="form-control d-inline-block" style="width: 80px; text-align: center;">
                <span class="ms-2">of <?= $totalPages; ?></span>
            </form>
        </li>
&emsp;
        <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $page + 1; ?>">Next</a>
            </li>
        <?php endif; ?>
    </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="scripts/script.js"></script>
</body>
</html>