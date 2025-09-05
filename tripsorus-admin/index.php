<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}
require '../db.php';
$adminId = $_SESSION['user_id'];

if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

$totalProperties = 0;
$totalBookings = 0;
$activeGuests = 0;
$monthlyRevenue = 0;
$recentBookings = [];
$recentProperties = [];
$monthlyRevenues = array_fill(0, 12, 0);
$adminName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
$error = "";

try {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM user WHERE id = ?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    $adminName = $admin ? $admin['first_name'] . ' ' . $admin['last_name'] : "Admin";
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM properties WHERE admin_id = ?");
    $stmt->execute([$adminId]);
    $totalProperties = (int) $stmt->fetchColumn();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM bookings 
        INNER JOIN properties ON bookings.property_id = properties.id
        WHERE properties.admin_id = ?
    ");
    $stmt->execute([$adminId]);
    $totalBookings = (int) $stmt->fetchColumn();
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT bookings.booking_code)
        FROM bookings
        INNER JOIN properties ON bookings.property_id = properties.id
        WHERE properties.admin_id = ? AND bookings.check_out >= CURDATE()
    ");
    $stmt->execute([$adminId]);
    $activeGuests = (int) $stmt->fetchColumn();
    $stmt = $pdo->prepare("
        SELECT SUM(bookings.amount)
        FROM bookings
        INNER JOIN properties ON bookings.property_id = properties.id
        WHERE properties.admin_id = ? 
        AND MONTH(bookings.created_at) = MONTH(CURDATE())
        AND YEAR(bookings.created_at) = YEAR(CURDATE())
    ");
    $stmt->execute([$adminId]);
    $monthlyRevenue = (float) $stmt->fetchColumn();
    $stmt = $pdo->prepare("
        SELECT bookings.*, properties.name AS property_name
        FROM bookings
        INNER JOIN properties ON bookings.property_id = properties.id
        WHERE properties.admin_id = ?
        ORDER BY bookings.created_at DESC
        LIMIT 2
    ");
    $stmt->execute([$adminId]);
    $recentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("
        SELECT * 
        FROM properties 
        WHERE admin_id = ? 
        ORDER BY created_at DESC 
        LIMIT 2
    ");
    $stmt->execute([$adminId]);
    $recentProperties = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Unable to load dashboard data. Please try again later.";
    error_log("Database error: " . $e->getMessage());
}

$monthlyRevenues = array_map('intval', $monthlyRevenues);

function safeOutput($value, $default = '') {
    return htmlspecialchars($value ?? $default, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - TRIPSORUS Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" href="../images/favicon.ico" type="image/ico" />
  <link rel="stylesheet" href="styles/style.css">
</head>

<body>
  <?php include 'sidebar.php'; ?>
  <div class="main-content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dashboard Overview <span class="admin-id-badge">Admin ID:
            <?php echo safeOutput($adminId); ?>
          </span></h2>
        <div class="d-flex">
          <div class="input-group me-3" style="width: 250px;">
            <input type="text" class="form-control" placeholder="Search...">
            <button class="btn btn-outline-secondary" type="button">
              <i class="fas fa-search"></i>
            </button>
          </div>

          <!-- User Profile Dropdown -->
          <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle user-dropdown d-flex align-items-center" type="button"
              id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="user-avatar">
                <span>
                  <?php echo substr(safeOutput($adminName), 0, 1); ?>
                </span>
              </div>
              <span>
                <?php echo safeOutput($adminName); ?>
              </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li class="user-info">
                <div class="user-name">
                  <?php echo safeOutput($adminName); ?>
                </div>
                <div class="user-role">Administrator</div>
                <small class="text-muted">ID:
                  <?php echo safeOutput($adminId); ?>
                </small>
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

      <!-- Display error message if any -->
      <?php if (!empty($error)): ?>
      <div class="alert alert-warning error-alert alert-dismissible fade show" role="alert">
        <strong>Warning!</strong>
        <?php echo safeOutput($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <!-- Stats Cards -->
      <div class="row">
        <div class="col-md-3">
          <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-hotel"></i>
            <div class="count">
              <?php echo safeOutput($totalProperties); ?>
            </div>
            <div class="title">Your Properties</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card" style="background: linear-gradient(135deg, #ff758c 0%, #ff7eb3 100%);">
            <i class="fas fa-calendar-check"></i>
            <div class="count">
              <?php echo safeOutput($totalBookings); ?>
            </div>
            <div class="title">Total Bookings</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <i class="fas fa-users"></i>
            <div class="count">
              <?php echo safeOutput($activeGuests); ?>
            </div>
            <div class="title">Active Guests</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <i class="fas fa-coins"></i>
            <div class="count">₹
              <?php echo number_format($monthlyRevenue); ?>
            </div>
            <div class="title">This Month Revenue</div>
          </div>
        </div>
      </div>
      <!-- Recent Bookings -->
      <div class="row mt-4">
        <div class="col-md-8">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Recent Bookings</h5>
              <a href="bookings.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Property</th>
                      <th>First Name</th>
                      <th>Last Name</th>
                      <th>Check-In</th>
                      <th>Check-Out</th>
                      <th>Amount</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($recentBookings) > 0): ?>
                    <?php foreach ($recentBookings as $booking): 
                  $statusClass = '';
                  if (isset($booking['status'])) {
                      if ($booking['status'] == 'Confirmed') $statusClass = 'bg-success';
                      else if ($booking['status'] == 'Pending') $statusClass = 'bg-warning text-dark';
                      else if ($booking['status'] == 'Cancelled') $statusClass = 'bg-danger';
                      else $statusClass = 'bg-secondary';
                  }
                ?>
                    <tr>
                      <td><?php echo safeOutput($booking['booking_code'] ?? 'N/A'); ?></td>
                      <td><?php echo safeOutput($booking['property_name'] ?? 'Unknown'); ?></td>
                      <td><?php echo safeOutput($booking['first_name'] ?? 'N/A'); ?></td>
                      <td><?php echo safeOutput($booking['last_name'] ?? 'N/A'); ?></td>
                      <td>
                        <?php 
                    if (isset($booking['check_in'])) {
                        echo date('d M Y', strtotime($booking['check_in']));
                    } else {
                        echo 'N/A';
                    }
                    ?>
                      </td>
                      <td>
                        <?php 
                    if (isset($booking['check_out'])) {
                        echo date('d M Y', strtotime($booking['check_out']));
                    } else {
                        echo 'N/A';
                    }
                    ?>
                      </td>
                      <td>₹<?php echo isset($booking['amount']) ? number_format($booking['amount']) : '0'; ?></td>
                      <td>
                        <span class="badge <?php echo $statusClass; ?>">
                          <?php echo isset($booking['status']) ? safeOutput($booking['status']) : 'Unknown'; ?>
                        </span>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (count($recentBookings) < 2): ?>
                    <?php for ($i = count($recentBookings); $i < 2; $i++): ?>
                    <tr>
                      <td colspan="8" class="text-center text-muted py-3">
                        <i class="fas fa-calendar-times me-2"></i>No booking data available
                      </td>
                    </tr>
                    <?php endfor; ?>
                    <?php endif; ?>

                    <?php else: ?>
                    <?php for ($i = 0; $i < 2; $i++): ?>
                    <tr>
                      <td colspan="8" class="text-center text-muted py-3">
                        <i class="fas fa-calendar-times me-2"></i>No bookings found
                      </td>
                    </tr>
                    <?php endfor; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Properties -->
        <div class="col-md-4">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Your Properties</h5>
              <a href="properties.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
              <div class="list-group">
                <?php if (count($recentProperties) > 0): ?>
                <?php foreach ($recentProperties as $property): 
        $addedTime = isset($property['updated_at']) ? strtotime($property['updated_at']) : time();
        $timeDiff = time() - $addedTime;

        if ($timeDiff < 86400) {
            $timeText = 'Today';
        } elseif ($timeDiff < 172800) {
            $timeText = 'Yesterday';
        } elseif ($timeDiff < 604800) {
            $days = floor($timeDiff / 86400);
            $timeText = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($timeDiff < 2592000) {
            $weeks = floor($timeDiff / 604800);
            $timeText = $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
        } else {
            $months = floor($timeDiff / 2592000);
            $timeText = $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
        }
    ?>
                <a href="#" class="list-group-item list-group-item-action">
                  <div class="d-flex w-100 justify-content-between">
                    <small>ID: <?php echo safeOutput($property['id']); ?></small>
                    <h6 class="mb-1"><?php echo safeOutput($property['name'] ?? 'Unnamed Property'); ?></h6>
                    <small class="text-muted">Added: <?php echo $timeText; ?></small>
                  </div>
                </a>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="text-center py-4 text-muted">No properties found</div>
                <?php endif; ?>

              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Revenue Chart -->
      <div class="row mt-4">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Monthly Revenue (Current Year)</h5>
            </div>
            <div class="card-body">
              <canvas id="revenueChart" height="100"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
  const ctx = document.getElementById('revenueChart').getContext('2d');
  const revenueChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [{
        label: 'Revenue (₹)',
        data: JSON.parse(`<?php echo json_encode($monthlyRevenues); ?>`),

        backgroundColor: 'rgba(54, 162, 235, 0.7)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return '₹' + value.toLocaleString();
            }
          }
        }
      }
    }
  });
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
  </script>
</body>

</html>