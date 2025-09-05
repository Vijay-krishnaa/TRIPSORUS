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
require_once 'db.php';

// Initialize variables
$admins = [];
$error = "";
$success = "";
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';

// Handle admin actions (add, edit, delete, activate, deactivate)
if (isset($_POST['add_admin'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $userType = $_POST['user_type'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO user (first_name, last_name, email, password, phone, user_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$firstName, $lastName, $email, $password, $phone, $userType]);
        
        $success = "Admin account created successfully!";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Email address already exists!";
        } else {
            $error = "Error creating admin account: " . $e->getMessage();
        }
    }
}

if (isset($_POST['update_admin'])) {
    $adminId = $_POST['admin_id'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $userType = $_POST['user_type'];
    
    try {
        $stmt = $pdo->prepare("UPDATE user SET first_name = ?, last_name = ?, email = ?, phone = ?, user_type = ? WHERE id = ?");
        $stmt->execute([$firstName, $lastName, $email, $phone, $userType, $adminId]);
        
        $success = "Admin account updated successfully!";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Email address already exists!";
        } else {
            $error = "Error updating admin account: " . $e->getMessage();
        }
    }
}

if (isset($_GET['delete_id'])) {
    $adminId = $_GET['delete_id'];
    
    // Prevent super admin from deleting themselves
    if ($adminId == $_SESSION['user_id']) {
        $error = "You cannot delete your own account!";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM user WHERE id = ?");
            $stmt->execute([$adminId]);
            
            $success = "Admin account deleted successfully!";
        } catch (PDOException $e) {
            $error = "Error deleting admin account: " . $e->getMessage();
        }
    }
}

if (isset($_GET['toggle_status'])) {
    $adminId = $_GET['toggle_status'];
    $currentStatus = $_GET['current_status'];
    
    // Prevent super admin from deactivating themselves
    if ($adminId == $_SESSION['user_id'] && $currentStatus == 'active') {
        $error = "You cannot deactivate your own account!";
    } else {
        $newStatus = $currentStatus == 'active' ? 'inactive' : 'active';
        
        try {
            $stmt = $pdo->prepare("UPDATE user SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $adminId]);
            
            $success = "Admin account status updated successfully!";
        } catch (PDOException $e) {
            $error = "Error updating admin status: " . $e->getMessage();
        }
    }
}

// Fetch admins with filters
try {
    $query = "SELECT * FROM user WHERE user_type IN ('admin', 'super_admin')";
    
    $conditions = [];
    $params = [];
    
    // Add search condition
    if (!empty($search)) {
        $conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    // Add status condition
    if ($status_filter !== 'all') {
        $conditions[] = "status = ?";
        $params[] = $status_filter;
    }
    
    // Add role condition
    if ($role_filter !== 'all') {
        $conditions[] = "user_type = ?";
        $params[] = $role_filter;
    }
    
    // Add WHERE clause if there are conditions
    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }
    
    // Add ORDER BY
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Unable to load admins: " . $e->getMessage();
}

// Get admin name
$adminName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admins Management - TRIPSORUS Super Admin</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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
    background-color: #f8f9fa;
  }

  .user-name {
    font-weight: bold;
  }

  .user-role {
    font-size: 0.9rem;
    color: #6c757d;
  }

  .filter-section {
    background-color: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }

  .status-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
  }

  .bg-active {
    background-color: #d4edda;
    color: #155724;
  }

  .bg-inactive {
    background-color: #f8d7da;
    color: #721c24;
  }

  .role-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
  }

  .bg-super-admin {
    background-color: #6f42c1;
    color: white;
  }

  .bg-admin {
    background-color: #007bff;
    color: white;
  }

  .admin-table {
    background-color: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }

  .table thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
  }

  .admin-avatar {
    width: 40px;
    height: 40px;
    background-color: #007bff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    margin-right: 10px;
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
  <!-- Sidebar -->
  <nav class="sidebar" id="sidebar">
    <div class="position-sticky pt-3">
      <h4 class="text-white text-center mb-4">
        <i class="fas fa-crown me-2"></i>Super Admin
      </h4>
      <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="properties.php">
            <i class="fas fa-hotel me-2"></i>All Properties
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="admins.php">
            <i class="fas fa-users-cog me-2"></i>Manage Admins
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="bookings.php">
            <i class="fas fa-calendar-check me-2"></i>Bookings
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="reports.php">
            <i class="fas fa-chart-bar me-2"></i>Reports
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="settings.php">
            <i class="fas fa-cog me-2"></i>Settings
          </a>
        </li>
        <li class="nav-item mt-3">
          <a class="nav-link text-danger" href="../logout.php">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Admins Management <span class="admin-id-badge">Super Admin</span></h2>
        <div class="d-flex">
          <button class="btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="fas fa-plus me-2"></i>Add New Admin
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

      <!-- Filters Section -->
      <div class="filter-section">
        <form method="GET" action="admins.php">
          <div class="row">
            <div class="col-md-4">
              <div class="input-group">
                <input type="text" class="form-control" placeholder="Search admins..." name="search"
                  value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-primary" type="submit">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
            <div class="col-md-3">
              <select class="form-select" name="role">
                <option value="all" <?php echo $role_filter === 'all' ? 'selected' : ''; ?>>All Roles</option>
                <option value="super_admin" <?php echo $role_filter === 'super_admin' ? 'selected' : ''; ?>>Super Admin
                </option>
                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
              </select>
            </div>
            <div class="col-md-3">
              <select class="form-select" name="status">
                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive
                </option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
            </div>
          </div>
        </form>
      </div>

      <!-- Admins Table -->
      <div class="admin-table">
        <div class="table-responsive">
          <table class="table table-hover" id="adminsTable">
            <thead>
              <tr>
                <th>Admin</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created</th>
                <th>Last Login</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($admins) > 0): ?>
              <?php foreach ($admins as $admin): 
                  // Determine status badge class
                  $statusClass = $admin['status'] == 'active' ? 'bg-active' : 'bg-inactive';
                  $statusText = $admin['status'] == 'active' ? 'Active' : 'Inactive';
                  
                  // Determine role badge class
                  $roleClass = $admin['user_type'] == 'super_admin' ? 'bg-super-admin' : 'bg-admin';
                  $roleText = $admin['user_type'] == 'super_admin' ? 'Super Admin' : 'Admin';
                  
                  // Format dates
                  $createdDate = date('M j, Y', strtotime($admin['created_at']));
                  $lastLogin = !empty($admin['last_login']) ? date('M j, Y', strtotime($admin['last_login'])) : 'Never';
                  
                  // Get initials for avatar
                  $initials = substr($admin['first_name'], 0, 1) . substr($admin['last_name'], 0, 1);
                ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="admin-avatar">
                      <?php echo strtoupper($initials); ?>
                    </div>
                    <div>
                      <div class="fw-bold">
                        <?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></div>
                      <small class="text-muted">ID: <?php echo $admin['id']; ?></small>
                    </div>
                  </div>
                </td>
                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                <td><?php echo htmlspecialchars($admin['phone'] ?? 'N/A'); ?></td>
                <td>
                  <span class="badge role-badge <?php echo $roleClass; ?>">
                    <?php echo $roleText; ?>
                  </span>
                </td>
                <td>
                  <span class="badge status-badge <?php echo $statusClass; ?>">
                    <?php echo $statusText; ?>
                  </span>
                </td>
                <td><?php echo $createdDate; ?></td>
                <td><?php echo $lastLogin; ?></td>
                <td>
                  <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                      data-bs-target="#editAdminModal<?php echo $admin['id']; ?>">
                      <i class="fas fa-edit"></i>
                    </button>
                    <?php if ($admin['id'] != $_SESSION['user_id']): ?>
                    <a href="admins.php?toggle_status=<?php echo $admin['id']; ?>&current_status=<?php echo $admin['status']; ?>"
                      class="btn btn-sm <?php echo $admin['status'] == 'active' ? 'btn-outline-warning' : 'btn-outline-success'; ?>">
                      <i class="fas <?php echo $admin['status'] == 'active' ? 'fa-times' : 'fa-check'; ?>"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                      data-bs-target="#deleteModal<?php echo $admin['id']; ?>">
                      <i class="fas fa-trash"></i>
                    </button>
                    <?php else: ?>
                    <button class="btn btn-sm btn-outline-secondary" disabled>
                      <i class="fas fa-user-lock"></i>
                    </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>

              <!-- Edit Modal -->
              <div class="modal fade" id="editAdminModal<?php echo $admin['id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Admin:
                        <?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="admins.php">
                      <div class="modal-body">
                        <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name"
                              value="<?php echo htmlspecialchars($admin['first_name']); ?>" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name"
                              value="<?php echo htmlspecialchars($admin['last_name']); ?>" required>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Email Address</label>
                          <input type="email" class="form-control" name="email"
                            value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Phone Number</label>
                          <input type="tel" class="form-control" name="phone"
                            value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Role</label>
                          <select class="form-select" name="user_type" required>
                            <option value="admin" <?php echo $admin['user_type'] == 'admin' ? 'selected' : ''; ?>>Admin
                            </option>
                            <option value="super_admin"
                              <?php echo $admin['user_type'] == 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                          </select>
                        </div>
                        <div class="alert alert-info">
                          <small><i class="fas fa-info-circle me-1"></i> Leave password fields blank to keep current
                            password</small>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">New Password</label>
                          <input type="password" class="form-control" name="password">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Confirm Password</label>
                          <input type="password" class="form-control" name="confirm_password">
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_admin" class="btn btn-primary">Update Admin</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              <!-- Delete Modal -->
              <div class="modal fade" id="deleteModal<?php echo $admin['id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Confirm Delete</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <p>Are you sure you want to delete the admin account for
                        "<strong><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></strong>"?
                      </p>
                      <p class="text-danger">This action cannot be undone. All properties and data associated with this
                        admin will be reassigned to the super admin.</p>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <a href="admins.php?delete_id=<?php echo $admin['id']; ?>" class="btn btn-danger">Delete Admin</a>
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
              <?php else: ?>
              <tr>
                <td colspan="8" class="text-center py-4">
                  <i class="fas fa-users-slash fa-2x mb-2"></i>
                  <h5>No Admins Found</h5>
                  <p class="text-muted">There are no admins matching your search criteria.</p>
                  <a href="admins.php" class="btn btn-primary">Clear Filters</a>
                </td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Stats Section -->
      <div class="row mt-4">
        <div class="col-md-3">
          <div class="card text-center">
            <div class="card-body">
              <h3 class="card-title"><?php echo count($admins); ?></h3>
              <p class="card-text">Total Admins</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center">
            <div class="card-body">
              <h3 class="card-title">
                <?php echo count(array_filter($admins, function($a) { return $a['user_type'] == 'super_admin'; })); ?>
              </h3>
              <p class="card-text">Super Admins</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center">
            <div class="card-body">
              <h3 class="card-title">
                <?php echo count(array_filter($admins, function($a) { return $a['status'] == 'active'; })); ?></h3>
              <p class="card-text">Active Admins</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center">
            <div class="card-body">
              <h3 class="card-title">
                <?php echo count(array_filter($admins, function($a) { return $a['status'] == 'inactive'; })); ?></h3>
              <p class="card-text">Inactive Admins</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Admin Modal -->
  <div class="modal fade" id="addAdminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Admin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="admins.php">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">First Name</label>
                <input type="text" class="form-control" name="first_name" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" class="form-control" name="last_name" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Email Address</label>
              <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Phone Number</label>
              <input type="tel" class="form-control" name="phone">
            </div>
            <div class="mb-3">
              <label class="form-label">Role</label>
              <select class="form-select" name="user_type" required>
                <option value="admin">Admin</option>
                <option value="super_admin">Super Admin</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" name="password" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" class="form-control" name="confirm_password" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="add_admin" class="btn btn-primary">Add Admin</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script>
  $(document).ready(function() {
    $('#adminsTable').DataTable({
      "pageLength": 10,
      "order": [
        [0, 'asc']
      ],
      "language": {
        "search": "Search admins:",
        "lengthMenu": "Show _MENU_ entries",
        "info": "Showing _START_ to _END_ of _TOTAL_ admins",
        "infoEmpty": "No admins available",
        "infoFiltered": "(filtered from _MAX_ total admins)"
      }
    });
  });
  </script>
</body>

</html>