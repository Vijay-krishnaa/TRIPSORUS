<?php
session_start();
$timeout_duration = 2700; 
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();     
    session_destroy();   
    echo "<script>
        alert('Session expired due to inactivity. Please login again.');
        window.location.href = '../index.php';
    </script>";
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time(); 

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'super_admin') {
    echo "<script>
        alert('Access denied. Super Admins only.');
        window.location.href = '../index.php';
    </script>";
    exit;
}
require_once '../db.php';
$stats = [];
$recentBookings = [];
$recentProperties = [];
$adminName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM properties");
    $stmt->execute();
    $stats['total_properties'] = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM user WHERE user_type = 'admin'");
    $stmt->execute();
    $stats['total_admins'] = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM bookings WHERE status = 'Confirmed'");
    $stmt->execute();
    $stats['total_revenue'] = $stmt->fetchColumn() ?? 0;
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings");
    $stmt->execute();
    $stats['total_bookings'] = $stmt->fetchColumn();
    $stmt = $pdo->prepare("
        SELECT bookings.*, properties.name AS property_name
        FROM bookings
        INNER JOIN properties ON bookings.property_id = properties.id
        ORDER BY bookings.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("
        SELECT p.*, u.first_name, u.last_name 
        FROM properties p 
        LEFT JOIN user u ON p.admin_id = u.id 
        ORDER BY p.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recentProperties = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Unable to load dashboard data. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TRIPSORUS - Super Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
  :root {
    --primary-color: #007bff;
    --secondary-color: #6c757d;
    --dark-color: #343a40;
    --light-color: #f8f9fa;
  }

  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f5f7fa;
    overflow-x: hidden;
  }

  .sidebar {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    color: white;
    min-height: 100vh;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    position: fixed;
    width: 250px;
    transition: all 0.3s;
    z-index: 1000;
  }

  .sidebar .nav-link {
    color: rgba(255, 255, 255, 0.8);
    padding: 12px 15px;
    margin: 5px 0;
    border-radius: 5px;
    transition: all 0.3s;
  }

  .sidebar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
  }

  .sidebar .nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
    font-weight: 500;
  }

  .sidebar .nav-link i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
  }

  .stats-card {
    border-radius: 10px;
    padding: 20px;
    color: white;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
  }

  .stats-card:hover {
    transform: translateY(-5px);
  }

  .stats-card i {
    font-size: 2rem;
    margin-bottom: 10px;
  }

  .stats-card .count {
    font-size: 2rem;
    font-weight: bold;
  }

  .stats-card .title {
    font-size: 0.9rem;
    opacity: 0.9;
  }

  .main-content {
    margin-left: 250px;
    transition: all 0.3s;
    padding: 20px;
  }

  .admin-id-badge {
    background-color: #6f42c1;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
  }

  .user-dropdown {
    background-color: #6f42c1;
    border: none;
  }

  .user-avatar {
    width: 30px;
    height: 30px;
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
  }

  .user-info {
    padding: 10px 15px;
    border-bottom: 1px solid #dee2e6;
  }

  .user-name {
    font-weight: bold;
  }

  .user-role {
    font-size: 0.9rem;
    color: #6c757d;
  }

  @media (max-width: 768px) {
    .sidebar {
      width: 0;
      position: fixed;
      z-index: 1000;
    }

    .sidebar.active {
      width: 250px;
    }

    .main-content {
      margin-left: 0;
    }
  }
  </style>
</head>

<body>
  <?php include 'sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Super Admin Dashboard <span class="admin-id-badge">ID: <?php echo $_SESSION['user_id']; ?></span></h2>
        <div class="d-flex">
          <div class="input-group me-3" style="width: 250px;">
            <input type="text" class="form-control" placeholder="Search...">
            <button class="btn btn-outline-secondary" type="button">
              <i class="fas fa-search"></i>
            </button>
          </div>

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

      <?php if (isset($error)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <!-- Stats Cards -->
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
        <div class="col">
          <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-hotel"></i>
            <div class="count"><?php echo $stats['total_properties']; ?></div>
            <div class="title">Total Properties</div>
          </div>
        </div>

        <div class="col">
          <div class="stats-card" style="background: linear-gradient(135deg, #ff758c 0%, #ff7eb3 100%);">
            <i class="fas fa-users-cog"></i>
            <div class="count"><?php echo $stats['total_admins']; ?></div>
            <div class="title">Administrators</div>
          </div>
        </div>

        <div class="col">
          <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <i class="fas fa-coins"></i>
            <div class="count">₹<?php echo number_format($stats['total_revenue']); ?></div>
            <div class="title">Total Revenue</div>
          </div>
        </div>

        <div class="col">
          <div class="stats-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <i class="fas fa-calendar-check"></i>
            <div class="count"><?php echo $stats['total_bookings']; ?></div>
            <div class="title">Total Bookings</div>
          </div>
        </div>

        <div class="col">
          <div class="stats-card" style="background: linear-gradient(135deg, #cd9cf2 0%, #f6f3ff 100%);">
            <i class="fas fa-star"></i>
            <div class="count"><?php echo count($recentProperties); ?></div>
            <div class="title">Recent Properties</div>
          </div>
        </div>
      </div>

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
                      <th>Booking Code</th>
                      <th>Property</th>
                      <th>Guest</th>
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
                        if ($booking['status'] == 'Confirmed') $statusClass = 'bg-success';
                        else if ($booking['status'] == 'Pending') $statusClass = 'bg-warning text-dark';
                        else if ($booking['status'] == 'Cancelled') $statusClass = 'bg-danger';
                        else $statusClass = 'bg-secondary';
                      ?>
                    <tr>
                      <td><?php echo htmlspecialchars($booking['booking_code']); ?></td>
                      <td><?php echo htmlspecialchars($booking['property_name']); ?></td>
                      <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                      <td><?php echo date('d M Y', strtotime($booking['check_in'])); ?></td>
                      <td><?php echo date('d M Y', strtotime($booking['check_out'])); ?></td>
                      <td>₹<?php echo number_format($booking['amount']); ?></td>
                      <td>
                        <span class="badge <?php echo $statusClass; ?>">
                          <?php echo htmlspecialchars($booking['status']); ?>
                        </span>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                      <td colspan="7" class="text-center text-muted py-3">
                        <i class="fas fa-calendar-times me-2"></i>No bookings found
                      </td>
                    </tr>
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
              <h5 class="mb-0">Recent Properties</h5>
              <a href="properties.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
              <div class="list-group">
                <?php if (count($recentProperties) > 0): ?>
                <?php foreach ($recentProperties as $property): 
                    $addedTime = strtotime($property['created_at']);
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
                    <small>ID: <?php echo htmlspecialchars($property['id']); ?></small>
                    <h6 class="mb-1"><?php echo htmlspecialchars($property['name']); ?></h6>
                    <small class="text-muted">Added: <?php echo $timeText; ?></small>
                  </div>
                  <small class="text-muted">By:
                    <?php echo htmlspecialchars($property['first_name'] . ' ' . $property['last_name']); ?></small>
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
        data: [125000, 189000, 142000, 178000, 156000, 210000, 185000, 230000, 205000, 245000, 198000,
          284760
        ],
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
  </script>
</body>

</html>