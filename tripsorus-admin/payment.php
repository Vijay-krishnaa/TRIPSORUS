<?php
require_once '../db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$adminId = $_SESSION['user_id'];
$adminName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

$totalRevenue = 0;
$completedPayments = 0;
$pendingPayments = 0;
$paidGuests = 0;
$error = "";
$success = "";
$bookings = [];
$page = 1;
$totalPages = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $bookingId = $_POST['booking_id'];
    
    try {
    
        error_log("Attempting to confirm payment for booking ID: $bookingId by admin ID: $adminId");
        $checkStmt = $pdo->prepare("
            SELECT b.booking_id 
            FROM bookings b
            INNER JOIN properties p ON b.property_id = p.id
            WHERE b.booking_id = ? AND p.admin_id = ?
        ");
        $checkStmt->execute([$bookingId, $adminId]);
        $bookingExists = $checkStmt->fetch();
        
        if (!$bookingExists) {
            $error = "Booking not found or you don't have permission to modify it.";
        } else {
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET status = 'Confirmed'
                WHERE booking_id = ?
            ");
            $stmt->execute([$bookingId]);
            
            if ($stmt->rowCount() > 0) {
                $success = "Payment confirmed successfully!";
                error_log("Successfully confirmed payment for booking ID: $bookingId");
            } else {
                $error = "Unable to confirm payment. It may already be confirmed.";
                error_log("No rows affected when confirming payment for booking ID: $bookingId");
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: Unable to confirm payment.";
        error_log("Payment confirmation error: " . $e->getMessage());
    }
}


try {
    $stmt = $pdo->prepare("
        SELECT SUM(b.amount)
        FROM bookings b
        INNER JOIN properties p ON b.property_id = p.id
        WHERE p.admin_id = ?
    ");
    $stmt->execute([$adminId]);
    $totalRevenue = (float) $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM bookings b
        INNER JOIN properties p ON b.property_id = p.id
        WHERE p.admin_id = ? AND b.status = 'Confirmed'
    ");
    $stmt->execute([$adminId]);
    $completedPayments = (int) $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM bookings b
        INNER JOIN properties p ON b.property_id = p.id
        WHERE p.admin_id = ? AND b.status = 'Pending'
    ");
    $stmt->execute([$adminId]);
    $pendingPayments = (int) $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT CONCAT(b.first_name, ' ', b.last_name))
        FROM bookings b
        INNER JOIN properties p ON b.property_id = p.id
        WHERE p.admin_id = ? AND b.status = 'Confirmed'
    ");
    $stmt->execute([$adminId]);
    $paidGuests = (int) $stmt->fetchColumn();
    
    $limit = 5;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    $totalStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM bookings b
        INNER JOIN properties p ON b.property_id = p.id
        WHERE p.admin_id = ?
    ");
    $totalStmt->execute([$adminId]);
    $totalBookings = $totalStmt->fetchColumn();
    $totalPages = ceil($totalBookings / $limit);
    
    $stmt = $pdo->prepare("
        SELECT b.*, p.name as property_name
        FROM bookings b
        INNER JOIN properties p ON b.property_id = p.id
        WHERE p.admin_id = :admin_id
        ORDER BY b.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':admin_id', $adminId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $bookings = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Unable to load payment data. Please try again later.";
    error_log("Database error: " . $e->getMessage());
}

function safeOutput($value, $default = '') {
    return htmlspecialchars($value ?? $default, ENT_QUOTES, 'UTF-8');
}

function formatDate($dateString) {
    if (empty($dateString)) return 'N/A';
    try {
        $date = new DateTime($dateString);
        return $date->format('jS M y');
    } catch (Exception $e) {
        return 'Invalid Date';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - TRIPSORUS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
      <link rel="icon" href="../images/favicon.ico" type="image/ico" />
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
   <?php include 'sidebar.php'; ?>
    <!-- Header -->
    <div class="container">
        <header class="header">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="fas fa-credit-card me-2"></i> Payment Management</h1>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle user-dropdown d-flex align-items-center" type="button"
                      id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                      <div class="user-avatar">
                        <span><?php echo substr(safeOutput($adminName), 0, 1); ?></span>
                      </div>
                      <span><?php echo safeOutput($adminName); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                      <li class="user-info">
                        <div class="user-name"><?php echo safeOutput($adminName); ?></div>
                        <div class="user-role">Administrator</div>
                        <small class="text-muted">ID: <?php echo safeOutput($adminId); ?></small>
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
        </header>

        <?php if (!empty($error)): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Warning!</strong> <?php echo safeOutput($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> <?php echo safeOutput($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-coins"></i>
                    <div class="count">₹<?php echo number_format($totalRevenue, 2); ?></div>
                    <div class="title">Total Revenue</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #ff758c 0%, #ff7eb3 100%);">
                    <i class="fas fa-check-circle"></i>
                    <div class="count"><?php echo $completedPayments; ?></div>
                    <div class="title">Completed Payments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-clock"></i>
                    <div class="count"><?php echo $pendingPayments; ?></div>
                    <div class="title">Pending Payments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="fas fa-user-check"></i>
                    <div class="count"><?php echo $paidGuests; ?></div>
                    <div class="title">Paid Guests</div>
                </div>
            </div>
        </div>

        <!-- Payment Filters -->
        <div class="payment-filter">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label for="paymentStatusFilter" class="form-label">Payment Status</label>
                    <select class="form-select" id="paymentStatusFilter">
                        <option value="">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label for="dateRangeFilter" class="form-label">Date Range</label>
                    <input type="text" class="form-control" id="dateRangeFilter" placeholder="Select date range">
                </div>
                <div class="col-md-3 mb-2">
                    <label for="searchFilter" class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchFilter" placeholder="Search bookings...">
                </div>
                <div class="col-md-3 mb-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Payments</h5>
                <div class="d-flex">
                    <button class="btn btn-sm btn-outline-secondary me-2">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                    <div class="input-group" style="width: 250px;">
                        <input type="text" class="form-control" placeholder="Search payments...">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Check-In Date</th>
                                <th>Guest Name</th>
                                <th>Booking Code</th>
                                <th>Property</th>
                                <th>Payment Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($bookings) > 0): ?>
                                <?php foreach ($bookings as $booking): 
                                    $statusClass = '';
                                    $statusText = '';
                                    if (isset($booking['status'])) {
                                        if ($booking['status'] == 'Confirmed') {
                                            $statusClass = 'bg-success';
                                            $statusText = 'Completed';
                                        } else if ($booking['status'] == 'Pending') {
                                            $statusClass = 'bg-warning text-dark';
                                            $statusText = 'Pending';
                                        } else if ($booking['status'] == 'Cancelled') {
                                            $statusClass = 'bg-danger';
                                            $statusText = 'Cancelled';
                                        } else {
                                            $statusClass = 'bg-secondary';
                                            $statusText = 'Unknown';
                                        }
                                    }
                                ?>
                                <tr>
                                    <td><?php echo formatDate($booking['check_in'] ?? ''); ?></td>
                                    <td><?php echo safeOutput($booking['first_name'] ?? '') . ' ' . safeOutput($booking['last_name'] ?? ''); ?></td>
                                    <td><?php echo safeOutput($booking['booking_code'] ?? 'N/A'); ?></td>
                                    <td><?php echo safeOutput($booking['property_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo safeOutput($booking['payment_type'] ?? 'N/A'); ?></td>
                                    <td>₹<?php echo isset($booking['amount']) ? number_format($booking['amount'], 2) : '0'; ?></td>
                                    <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                    <td>
                                        <div class="d-flex">
                                            <button class="btn btn-sm btn-outline-primary btn-action me-1" data-bs-toggle="tooltip" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (isset($booking['status']) && $booking['status'] == 'Pending'): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">

                                                <button type="submit" name="confirm_payment" class="btn btn-sm btn-success btn-action" 
                                                    data-bs-toggle="tooltip" title="Confirm Payment" 
                                                    onclick="return confirm('Are you sure you want to confirm this payment?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">No payments found</td>
                                </tr>
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
                        
                        <li class="page-item">
                            <form method="get" class="d-inline">
                                <input type="number" name="page" value="<?= $page; ?>" min="1" max="<?= $totalPages; ?>" 
                                       onchange="this.form.submit()" 
                                       class="form-control d-inline-block" style="width: 80px; text-align: center;">
                                <span class="ms-2">of <?= $totalPages; ?></span>
                            </form>
                        </li>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link ms-3" href="?page=<?= $page + 1; ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        
        document.getElementById('searchFilter').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Filter by payment status
        document.getElementById('paymentStatusFilter').addEventListener('change', function() {
            const status = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const statusBadge = row.querySelector('.badge');
                if (!statusBadge) return;
                
                const rowStatus = statusBadge.textContent.toLowerCase();
                if (!status || rowStatus.includes(status)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>