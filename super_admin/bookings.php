<?php
session_start();
$timeout_duration = 900;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
  session_unset();
  session_destroy();
  echo "<script>
        alert('Session expired due to inactivity. Please login again.');
        window.location.href = 'index.php';
    </script>";
  exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'super_admin') {
  echo "<script>
        alert('Access denied. Super Admins only.');
        window.location.href = 'index.php';
    </script>";
  exit;
}

require_once '../db.php';

$bookings = [];
$error = "";
$success = "";
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
if (isset($_POST['update_status'])) {
  $booking_id = $_POST['booking_id'];
  $new_status = $_POST['status'];

  try {
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
    $stmt->execute([$new_status, $booking_id]);

    $success = "Booking status updated successfully!";
  } catch (PDOException $e) {
    $error = "Error updating booking status: " . $e->getMessage();
  }
}

try {
  $query = "
        SELECT b.*, p.name as property_name, 
               CONCAT(u.first_name, ' ', u.last_name) as admin_name,
               rt.name as room_type_name
        FROM bookings b
        INNER JOIN properties p ON b.property_id = p.id 
        LEFT JOIN user u ON p.admin_id = u.id
        LEFT JOIN room_types rt ON b.room_type_id = rt.id
    ";

  $conditions = [];
  $params = [];
  if (!empty($search)) {
    $conditions[] = "(b.booking_code LIKE ? OR b.guest_name LIKE ? OR p.name LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
  }

  if ($status_filter !== 'all') {
    $conditions[] = "b.status = ?";
    $params[] = $status_filter;
  }

  if (!empty($date_filter)) {
    $conditions[] = "DATE(b.check_in) = ? OR DATE(b.check_out) = ?";
    $params[] = $date_filter;
    $params[] = $date_filter;
  }

  if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
  }
  $query .= " ORDER BY b.created_at DESC";

  $stmt = $pdo->prepare($query);
  $stmt->execute($params);
  $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
  $error = "Unable to load bookings: " . $e->getMessage();
}
$adminName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bookings Management - TRIPSORUS Super Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="style.css">

</head>

<body>
  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>
  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Bookings Management <span class="admin-id-badge">Super Admin</span></h2>
        <div class="d-flex">
          <!-- User Profile Dropdown -->
          <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle user-dropdown d-flex align-items-center" type="button"
              id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="user-avatar">
                <span><?php echo substr($adminName, 0, 1); ?></span>
              </div>
              <span><?php echo $adminName; ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li class="user-info">
                <div class="user-name"><?php echo $adminName; ?></div>
                <div class="user-role">Super Administrator</div>
                <small class="text-muted">ID: <?php echo $_SESSION['user_id']; ?></small>
              </li>
              <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> My Profile</a></li>
              <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
              <li><a class="dropdown-item" href="#"><i class="fas fa-bell me-2"></i> Notifications</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>
                  Logout</a></li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Display messages -->
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php echo $error; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?php echo $success; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <!-- Filters Section -->
      <div class="filter-section">
        <form method="GET" action="bookings.php">
          <div class="row">
            <div class="col-md-4">
              <div class="input-group">
                <input type="text" class="form-control" placeholder="Search bookings..." name="search"
                  value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-primary" type="submit">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
            <div class="col-md-3">
              <select class="form-select" name="status">
                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Confirmed" <?php echo $status_filter === 'Confirmed' ? 'selected' : ''; ?>>Confirmed
                </option>
                <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled
                </option>
              </select>
            </div>
            <div class="col-md-3">
              <input type="date" class="form-control" name="date" value="<?php echo htmlspecialchars($date_filter); ?>"
                placeholder="Filter by date">
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
            </div>
          </div>
        </form>
      </div>

      <!-- Bookings Table -->
      <div class="booking-table">
        <div class="table-responsive">
          <table class="table table-hover" id="bookingsTable">
            <thead>
              <tr>
                <th>Booking Code</th>
                <th>Property</th>
                <th>Guest</th>
                <th>Room Type</th>
                <th>Check-In</th>
                <th>Check-Out</th>
                <th>Amount</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($bookings) > 0): ?>
                <?php foreach ($bookings as $booking):
                  // Determine status badge class
                  $statusClass = '';
                  if ($booking['status'] == 'Confirmed')
                    $statusClass = 'bg-confirmed';
                  else if ($booking['status'] == 'Pending')
                    $statusClass = 'bg-pending';
                  else if ($booking['status'] == 'Cancelled')
                    $statusClass = 'bg-cancelled';

                  // Format dates
                  $checkIn = date('M j, Y', strtotime($booking['check_in']));
                  $checkOut = date('M j, Y', strtotime($booking['check_out']));
                  $createdAt = date('M j, Y', strtotime($booking['created_at']));
                  ?>
                  <tr>
                    <td>
                      <strong><?php echo htmlspecialchars($booking['booking_code']); ?></strong>
                      <br>
                      <small class="text-muted"><?php echo $createdAt; ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($booking['property_name']); ?></td>
                    <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                    <td><?php echo htmlspecialchars($booking['room_type_name'] ?? 'N/A'); ?></td>
                    <td><?php echo $checkIn; ?></td>
                    <td><?php echo $checkOut; ?></td>
                    <td>₹<?php echo number_format($booking['amount'], 2); ?></td>
                    <td>
                      <?php
                      $paymentType = $booking['payment_type'] ?? 'N/A';
                      $paymentBadgeClass = 'bg-secondary';
                      if ($paymentType == 'credit_card')
                        $paymentBadgeClass = 'bg-primary';
                      if ($paymentType == 'pay_at_property')
                        $paymentBadgeClass = 'bg-warning text-dark';
                      ?>
                      <span class="badge <?php echo $paymentBadgeClass; ?>">
                        <?php
                        if ($paymentType == 'credit_card')
                          echo 'Credit Card';
                        else if ($paymentType == 'pay_at_property')
                          echo 'Pay at Property';
                        else
                          echo $paymentType;
                        ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge status-badge <?php echo $statusClass; ?>">
                        <?php echo htmlspecialchars($booking['status']); ?>
                      </span>
                    </td>
                    <td>
                      <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                          data-bs-target="#viewModal<?php echo $booking['booking_id']; ?>">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                          data-bs-target="#editModal<?php echo $booking['booking_id']; ?>">
                          <i class="fas fa-edit"></i>
                        </button>
                      </div>
                    </td>
                  </tr>

                  <!-- View Modal -->
                  <div class="modal fade" id="viewModal<?php echo $booking['booking_id']; ?>" tabindex="-1"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Booking Details: <?php echo htmlspecialchars($booking['booking_code']); ?>
                          </h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="row">
                            <div class="col-md-6">
                              <h6>Guest Information</h6>
                              <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['guest_name']); ?></p>
                              <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['guest_email'] ?? 'N/A'); ?>
                              </p>
                              <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['guest_phone'] ?? 'N/A'); ?>
                              </p>
                            </div>
                            <div class="col-md-6">
                              <h6>Booking Information</h6>
                              <p><strong>Property:</strong> <?php echo htmlspecialchars($booking['property_name']); ?></p>
                              <p><strong>Room Type:</strong>
                                <?php echo htmlspecialchars($booking['room_type_name'] ?? 'N/A'); ?></p>
                              <p><strong>Meal Plan:</strong> <?php echo htmlspecialchars($booking['meal_type'] ?? 'N/A'); ?>
                              </p>
                            </div>
                          </div>
                          <div class="row mt-3">
                            <div class="col-md-6">
                              <h6>Dates</h6>
                              <p><strong>Check-In:</strong> <?php echo $checkIn; ?></p>
                              <p><strong>Check-Out:</strong> <?php echo $checkOut; ?></p>
                              <p><strong>Nights:</strong>
                                <?php
                                $checkInDate = new DateTime($booking['check_in']);
                                $checkOutDate = new DateTime($booking['check_out']);
                                $nights = $checkInDate->diff($checkOutDate)->days;
                                echo $nights;
                                ?>
                              </p>
                            </div>
                            <div class="col-md-6">
                              <h6>Payment Details</h6>
                              <p><strong>Amount:</strong> ₹<?php echo number_format($booking['amount'], 2); ?></p>
                              <p><strong>Payment Method:</strong>
                                <?php
                                if ($booking['payment_type'] == 'credit_card')
                                  echo 'Credit Card';
                                else if ($booking['payment_type'] == 'pay_at_property')
                                  echo 'Pay at Property';
                                else
                                  echo $booking['payment_type'] ?? 'N/A';
                                ?>
                              </p>
                              <p><strong>Status:</strong>
                                <span class="badge status-badge <?php echo $statusClass; ?>">
                                  <?php echo htmlspecialchars($booking['status']); ?>
                                </span>
                              </p>
                            </div>
                          </div>
                          <?php if (!empty($booking['gst_number'])): ?>
                            <div class="row mt-3">
                              <div class="col-12">
                                <h6>GST Information</h6>
                                <p><strong>GST Number:</strong> <?php echo htmlspecialchars($booking['gst_number']); ?></p>
                                <p><strong>Company Name:</strong>
                                  <?php echo htmlspecialchars($booking['gst_company_name'] ?? 'N/A'); ?></p>
                              </div>
                            </div>
                          <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Edit Modal -->
                  <div class="modal fade" id="editModal<?php echo $booking['booking_id']; ?>" tabindex="-1"
                    aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Update Booking Status</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="bookings.php">
                          <div class="modal-body">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                            <div class="mb-3">
                              <label class="form-label">Booking Code</label>
                              <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($booking['booking_code']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Guest Name</label>
                              <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($booking['guest_name']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Current Status</label>
                              <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($booking['status']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Update Status</label>
                              <select class="form-select" name="status" required>
                                <option value="Pending" <?php echo $booking['status'] == 'Pending' ? 'selected' : ''; ?>>
                                  Pending</option>
                                <option value="Confirmed" <?php echo $booking['status'] == 'Confirmed' ? 'selected' : ''; ?>>
                                  Confirmed</option>
                                <option value="Cancelled" <?php echo $booking['status'] == 'Cancelled' ? 'selected' : ''; ?>>
                                  Cancelled</option>
                              </select>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="10" class="text-center py-4">
                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                    <h5>No Bookings Found</h5>
                    <p class="text-muted">There are no bookings matching your search criteria.</p>
                    <a href="bookings.php" class="btn btn-primary">Clear Filters</a>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Export Section -->
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
          <span class="text-muted">Showing <?php echo count($bookings); ?> bookings</span>
        </div>
        <div>
          <button class="btn btn-outline-success">
            <i class="fas fa-download me-2"></i>Export as CSV
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#bookingsTable').DataTable({
        "pageLength": 10,
        "order": [
          [0, 'desc']
        ],
        "language": {
          "search": "Search bookings:",
          "lengthMenu": "Show _MENU_ entries",
          "info": "Showing _START_ to _END_ of _TOTAL_ bookings",
          "infoEmpty": "No bookings available",
          "infoFiltered": "(filtered from _MAX_ total bookings)"
        }
      });
    });
  </script>
</body>

</html>