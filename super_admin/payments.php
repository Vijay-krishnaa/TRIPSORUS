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

// Database connection using PDO
require_once '../db.php';

// Initialize variables
$payments = [];
$error = "";
$success = "";
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Handle payment status update
if (isset($_POST['update_status'])) {
  $payment_id = $_POST['payment_id'];
  $new_status = $_POST['status'];

  try {
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
    $stmt->execute([$new_status, $payment_id]);

    $success = "Payment status updated successfully!";
  } catch (PDOException $e) {
    $error = "Error updating payment status: " . $e->getMessage();
  }
}

// Fetch payments with filters
try {
  $query = "
        SELECT b.*, p.name as property_name, 
               CONCAT(b.first_name, ' ', b.last_name) as guest_name,
               u.first_name as admin_first_name, u.last_name as admin_last_name
        FROM bookings b
        INNER JOIN properties p ON b.property_id = p.id 
        LEFT JOIN user u ON p.admin_id = u.id
    ";

  $conditions = [];
  $params = [];

  // Add search condition
  if (!empty($search)) {
    $conditions[] = "(b.booking_code LIKE ? OR b.first_name LIKE ? OR b.last_name LIKE ? OR p.name LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
  }

  // Add status condition
  if ($status_filter !== 'all') {
    $conditions[] = "b.status = ?";
    $params[] = $status_filter;
  }

  // Add payment type condition
  if ($type_filter !== 'all') {
    $conditions[] = "b.payment_type = ?";
    $params[] = $type_filter;
  }

  // Add date range condition
  if (!empty($date_from) && !empty($date_to)) {
    $conditions[] = "DATE(b.created_at) BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
  } elseif (!empty($date_from)) {
    $conditions[] = "DATE(b.created_at) >= ?";
    $params[] = $date_from;
  } elseif (!empty($date_to)) {
    $conditions[] = "DATE(b.created_at) <= ?";
    $params[] = $date_to;
  }

  // Add WHERE clause if there are conditions
  if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
  }

  // Add ORDER BY
  $query .= " ORDER BY b.created_at DESC";

  $stmt = $pdo->prepare($query);
  $stmt->execute($params);
  $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Calculate totals
  $total_revenue = 0;
  $confirmed_revenue = 0;
  $pending_revenue = 0;

  foreach ($payments as $payment) {
    $total_revenue += $payment['amount'];
    if ($payment['status'] === 'Confirmed') {
      $confirmed_revenue += $payment['amount'];
    } else if ($payment['status'] === 'Pending') {
      $pending_revenue += $payment['amount'];
    }
  }

} catch (PDOException $e) {
  $error = "Unable to load payments: " . $e->getMessage();
}

// Get admin name
$adminName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payments Management - TRIPSORUS Super Admin</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Flatpickr for date range -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="style.css">

</head>

<body>
  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Payments Management <span class="admin-id-badge">Super Admin</span></h2>
        <div class="d-flex">
          <!-- Export Button -->
          <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#exportModal">
            <i class="fas fa-file-export me-2"></i>Export
          </button>

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

      <!-- Revenue Stats -->
      <div class="row mb-4">
        <div class="col-md-4">
          <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-money-bill-wave"></i>
            <div class="count">₹<?php echo number_format($total_revenue, 2); ?></div>
            <div class="title">Total Revenue</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <i class="fas fa-check-circle"></i>
            <div class="count">₹<?php echo number_format($confirmed_revenue, 2); ?></div>
            <div class="title">Confirmed Payments</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stats-card" style="background: linear-gradient(135deg, #ff758c 0%, #ff7eb3 100%);">
            <i class="fas fa-clock"></i>
            <div class="count">₹<?php echo number_format($pending_revenue, 2); ?></div>
            <div class="title">Pending Payments</div>
          </div>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="filter-section">
        <form method="GET" action="payments.php">
          <div class="row">
            <div class="col-md-3">
              <div class="input-group">
                <input type="text" class="form-control" placeholder="Search payments..." name="search"
                  value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-primary" type="submit">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
            <div class="col-md-2">
              <select class="form-select" name="status">
                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                <option value="Confirmed" <?php echo $status_filter === 'Confirmed' ? 'selected' : ''; ?>>Confirmed
                </option>
                <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled
                </option>
              </select>
            </div>
            <div class="col-md-2">
              <select class="form-select" name="type">
                <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>>All Types</option>
                <option value="credit_card" <?php echo $type_filter === 'credit_card' ? 'selected' : ''; ?>>Credit Card
                </option>
                <option value="pay_at_property" <?php echo $type_filter === 'pay_at_property' ? 'selected' : ''; ?>>Pay
                  at Property</option>
              </select>
            </div>
            <div class="col-md-2">
              <input type="text" class="form-control datepicker" placeholder="From Date" name="date_from"
                value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            <div class="col-md-2">
              <input type="text" class="form-control datepicker" placeholder="To Date" name="date_to"
                value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            <div class="col-md-1">
              <button type="submit" class="btn btn-primary w-100">Apply</button>
            </div>
          </div>
        </form>
      </div>

      <!-- Payments Table -->
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Payment Records</h5>
          <span class="badge bg-primary"><?php echo count($payments); ?> Records</span>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Booking ID</th>
                  <th>Guest</th>
                  <th>Property</th>
                  <th>Check-In/Out</th>
                  <th>Amount</th>
                  <th>Payment Type</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($payments) > 0): ?>
                  <?php foreach ($payments as $payment):
                    $statusClass = '';
                    if ($payment['status'] == 'Confirmed')
                      $statusClass = 'bg-success';
                    else if ($payment['status'] == 'Pending')
                      $statusClass = 'bg-warning text-dark';
                    else if ($payment['status'] == 'Cancelled')
                      $statusClass = 'bg-danger';
                    else
                      $statusClass = 'bg-secondary';

                    $paymentType = ucwords(str_replace('_', ' ', $payment['payment_type']));
                    $createdDate = date('M j, Y', strtotime($payment['created_at']));
                    ?>
                    <tr>
                      <td><?php echo htmlspecialchars($payment['booking_code']); ?></td>
                      <td><?php echo htmlspecialchars($payment['guest_name']); ?></td>
                      <td><?php echo htmlspecialchars($payment['property_name']); ?></td>
                      <td>
                        <?php echo date('M j', strtotime($payment['check_in'])); ?> -
                        <?php echo date('M j', strtotime($payment['check_out'])); ?>
                      </td>
                      <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                      <td><?php echo $paymentType; ?></td>
                      <td>
                        <span class="badge payment-status-badge <?php echo $statusClass; ?>">
                          <?php echo htmlspecialchars($payment['status']); ?>
                        </span>
                      </td>
                      <td><?php echo $createdDate; ?></td>
                      <td>
                        <div class="btn-group">
                          <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#detailsModal<?php echo $payment['booking_id']; ?>">
                            <i class="fas fa-eye"></i>
                          </button>
                          <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                            data-bs-target="#statusModal<?php echo $payment['booking_id']; ?>">
                            <i class="fas fa-edit"></i>
                          </button>
                        </div>
                      </td>
                    </tr>

                    <!-- Details Modal -->
                    <div class="modal fade" id="detailsModal<?php echo $payment['booking_id']; ?>" tabindex="-1"
                      aria-hidden="true">
                      <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Payment Details -
                              <?php echo htmlspecialchars($payment['booking_code']); ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <div class="row">
                              <div class="col-md-6">
                                <h6>Guest Information</h6>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($payment['guest_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($payment['guest_email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($payment['guest_phone']); ?></p>
                              </div>
                              <div class="col-md-6">
                                <h6>Booking Information</h6>
                                <p><strong>Property:</strong> <?php echo htmlspecialchars($payment['property_name']); ?></p>
                                <p><strong>Check-in:</strong> <?php echo date('M j, Y', strtotime($payment['check_in'])); ?>
                                </p>
                                <p><strong>Check-out:</strong>
                                  <?php echo date('M j, Y', strtotime($payment['check_out'])); ?></p>
                                <p><strong>Nights:</strong>
                                  <?php echo round((strtotime($payment['check_out']) - strtotime($payment['check_in'])) / (60 * 60 * 24)); ?>
                                </p>
                              </div>
                            </div>
                            <hr>
                            <div class="row">
                              <div class="col-md-6">
                                <h6>Payment Details</h6>
                                <p><strong>Amount:</strong> ₹<?php echo number_format($payment['amount'], 2); ?></p>
                                <p><strong>Payment Type:</strong> <?php echo $paymentType; ?></p>
                                <p><strong>Status:</strong> <span
                                    class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($payment['status']); ?></span>
                                </p>
                              </div>
                              <div class="col-md-6">
                                <h6>Additional Information</h6>
                                <p><strong>Booking Date:</strong>
                                  <?php echo date('M j, Y H:i', strtotime($payment['created_at'])); ?></p>
                                <?php if (!empty($payment['gst_number'])): ?>
                                  <p><strong>GST Number:</strong> <?php echo htmlspecialchars($payment['gst_number']); ?></p>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Status Update Modal -->
                    <div class="modal fade" id="statusModal<?php echo $payment['booking_id']; ?>" tabindex="-1"
                      aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Update Payment Status</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <form method="POST" action="payments.php">
                            <div class="modal-body">
                              <input type="hidden" name="payment_id" value="<?php echo $payment['booking_id']; ?>">
                              <div class="mb-3">
                                <label class="form-label">Current Status</label>
                                <p class="form-control-static">
                                  <span
                                    class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($payment['status']); ?></span>
                                </p>
                              </div>
                              <div class="mb-3">
                                <label for="status" class="form-label">New Status</label>
                                <select class="form-select" id="status" name="status" required>
                                  <option value="Confirmed" <?php echo $payment['status'] === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                  <option value="Pending" <?php echo $payment['status'] === 'Pending' ? 'selected' : ''; ?>>
                                    Pending</option>
                                  <option value="Cancelled" <?php echo $payment['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
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
                    <td colspan="9" class="text-center text-muted py-4">
                      <i class="fas fa-credit-card fa-3x mb-3"></i>
                      <h5>No Payments Found</h5>
                      <p>There are no payment records matching your search criteria.</p>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <?php if (count($payments) > 0): ?>
        <nav aria-label="Payments pagination" class="mt-4">
          <ul class="pagination justify-content-center">
            <li class="page-item disabled">
              <a class="page-link" href="#" tabindex="-1">Previous</a>
            </li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
              <a class="page-link" href="#">Next</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </div>

  <!-- Export Modal -->
  <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Export Payments Data</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="export_payments.php">
            <div class="mb-3">
              <label class="form-label">Export Format</label>
              <select class="form-select" name="format" required>
                <option value="csv">CSV</option>
                <option value="excel">Excel</option>
                <option value="pdf">PDF</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Date Range</label>
              <div class="row">
                <div class="col-md-6">
                  <input type="text" class="form-control datepicker" placeholder="From Date" name="export_date_from">
                </div>
                <div class="col-md-6">
                  <input type="text" class="form-control datepicker" placeholder="To Date" name="export_date_to">
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select class="form-select" name="export_status">
                <option value="all">All Status</option>
                <option value="Confirmed">Confirmed</option>
                <option value="Pending">Pending</option>
                <option value="Cancelled">Cancelled</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary">Export Data</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    // Initialize date pickers
    document.addEventListener('DOMContentLoaded', function () {
      flatpickr('.datepicker', {
        dateFormat: 'Y-m-d',
        allowInput: true
      });
    });
  </script>
</body>

</html>