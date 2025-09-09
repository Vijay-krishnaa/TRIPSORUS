<?php
session_start();
$timeout_duration = 2700;
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
$properties = [];
$error = "";
$success = "";
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';
if (isset($_GET['delete_id'])) {
  $property_id = $_GET['delete_id'];
  try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("DELETE FROM room_inventory WHERE property_id = ?");
    $stmt->execute([$property_id]);
    $stmt = $pdo->prepare("DELETE FROM room_types WHERE property_id = ?");
    $stmt->execute([$property_id]);
    $stmt = $pdo->prepare("DELETE FROM property_images WHERE property_id = ?");
    $stmt->execute([$property_id]);
    $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ?");
    $stmt->execute([$property_id]);
    $pdo->commit();

    $success = "Property deleted successfully!";
  } catch (PDOException $e) {
    $pdo->rollBack();
    $error = "Error deleting property: " . $e->getMessage();
  }
}

try {
  $query = "
  SELECT p.*, 
       u.first_name, 
       u.last_name, 
       COUNT(DISTINCT rt.id) AS room_types_count,
       COUNT(DISTINCT b.booking_id) AS bookings_count,
       pi.image_path AS property_image
FROM properties p 
LEFT JOIN user u ON p.admin_id = u.id 
LEFT JOIN room_types rt ON p.id = rt.property_id
LEFT JOIN bookings b ON p.id = b.property_id
LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_main = 1

";
  $conditions = [];
  $params = [];

  if (!empty($search)) {
    $conditions[] = "(p.name LIKE ? OR p.city LIKE ? OR p.country LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
  }
  if ($status_filter !== 'all') {
    $conditions[] = "p.id > 0";
  }

  if ($type_filter !== 'all') {
    $conditions[] = "p.type = ?";
    $params[] = $type_filter;
  }
  if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
  }
  $query .= " GROUP BY p.id ORDER BY p.created_at DESC";

  $stmt = $pdo->prepare($query);
  $stmt->execute($params);
  $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
  $error = "Unable to load properties: " . $e->getMessage();
}

$adminName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

if (isset($_GET['approve_id'])) {
  $property_id = intval($_GET['approve_id']);
  try {
    $stmt = $pdo->prepare("UPDATE properties SET status = 'approved' WHERE id = ?");
    $stmt->execute([$property_id]);
    $success = "Property approved successfully!";
  } catch (PDOException $e) {
    $error = "Error approving property: " . $e->getMessage();
  }
}

if (isset($_GET['reject_id'])) {
  $property_id = intval($_GET['reject_id']);
  try {
    $stmt = $pdo->prepare("UPDATE properties SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$property_id]);
    $success = "Property rejected successfully!";
  } catch (PDOException $e) {
    $error = "Error rejecting property: " . $e->getMessage();
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Properties Management - TRIPSORUS Super Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">

</head>

<body>
  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Properties Management <span class="admin-id-badge">Super Admin</span></h2>
        <div class="d-flex">
          <a href="add_property.php" class="btn btn-primary me-3">
            <i class="fas fa-plus me-2"></i>Add New Property
          </a>

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
        <form method="GET" action="properties.php">
          <div class="row">
            <div class="col-md-4">
              <div class="input-group">
                <input type="text" class="form-control" placeholder="Search properties..." name="search"
                  value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-primary" type="submit">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
            <div class="col-md-3">
              <select class="form-select" name="type">
                <option value="all" <?php echo $type_filter === 'all' ? 'selected' : ''; ?>>All Types</option>
                <option value="hotel" <?php echo $type_filter === 'hotel' ? 'selected' : ''; ?>>Hotel</option>
                <option value="resort" <?php echo $type_filter === 'resort' ? 'selected' : ''; ?>>Resort</option>
                <option value="villa" <?php echo $type_filter === 'villa' ? 'selected' : ''; ?>>Villa</option>
                <option value="apartment" <?php echo $type_filter === 'apartment' ? 'selected' : ''; ?>>Apartment
                </option>
                <option value="guesthouse" <?php echo $type_filter === 'guesthouse' ? 'selected' : ''; ?>>Guest House
                </option>
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
      <!-- Properties Grid -->
      <div class="row">
        <?php if (count($properties) > 0): ?>
          <?php foreach ($properties as $property):

            $createdDate = date('M j, Y', strtotime($property['created_at']));
            $imagePath = !empty($property['property_image'])
              ? "../tripsorus-admin/" . $property['property_image']
              : "../assets/default-hotel.jpg";
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
              <div class="card property-card h-100">
                <div class="position-relative">
                  <img src="<?php echo htmlspecialchars($imagePath); ?>" class="card-img-top property-img"
                    alt="<?php echo htmlspecialchars($property['name']); ?>">
                  <span class="badge bg-primary property-type-badge"><?php echo ucfirst($property['type']); ?></span>
                </div>
                <div class="card-body">
                  <h5 class="card-title"><?php echo htmlspecialchars($property['name']); ?></h5>
                  <p class="card-text text-muted">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    <?php echo htmlspecialchars($property['city'] . ', ' . $property['country']); ?>
                  </p>
                  <p class="card-text">
                    <?php echo strlen($property['description']) > 100 ? substr($property['description'], 0, 100) . '...' : $property['description']; ?>
                  </p>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="stats-badge bg-info">
                      <i class="fas fa-bed me-1"></i> <?php echo $property['room_types_count']; ?> Room Types
                    </span>
                    <span class="stats-badge bg-success">
                      <i class="fas fa-calendar-check me-1"></i> <?php echo $property['bookings_count']; ?> Bookings
                    </span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Added: <?php echo $createdDate; ?></small>
                    <small class="text-muted">By:
                      <?php echo htmlspecialchars($property['first_name'] . ' ' . $property['last_name']); ?></small>
                  </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between flex-wrap gap-2">
                  <a href="edit_property.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                  </a>

                  <a href="property_details.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-outline-info">
                    <i class="fas fa-eye me-1"></i> View
                  </a>

                  <?php if ($property['status'] === 'pending'): ?>
                    <a href="properties.php?approve_id=<?php echo $property['id']; ?>" class="btn btn-sm btn-outline-success">
                      <i class="fas fa-check me-1"></i> Approve
                    </a>
                    <a href="properties.php?reject_id=<?php echo $property['id']; ?>" class="btn btn-sm btn-outline-warning">
                      <i class="fas fa-times me-1"></i> Reject
                    </a>
                  <?php endif; ?>

                  <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                    data-bs-target="#deleteModal<?php echo $property['id']; ?>">
                    <i class="fas fa-trash me-1"></i> Delete
                  </button>
                </div>


              </div>
            </div>
            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal<?php echo $property['id']; ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p>Are you sure you want to delete the property
                      "<strong><?php echo htmlspecialchars($property['name']); ?></strong>"?</p>
                    <p class="text-danger">This action cannot be undone and will delete all related data including room
                      types and images.</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="properties.php?delete_id=<?php echo $property['id']; ?>" class="btn btn-danger">Delete
                      Property</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="alert alert-info text-center py-4">
              <i class="fas fa-hotel fa-3x mb-3"></i>
              <h4>No Properties Found</h4>
              <p>There are no properties matching your search criteria.</p>
              <a href="properties.php" class="btn btn-primary me-2">Clear Filters</a>
              <a href="add_property.php" class="btn btn-success">Add New Property</a>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Pagination -->
      <?php if (count($properties) > 0): ?>
        <nav aria-label="Properties pagination" class="mt-4">
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>