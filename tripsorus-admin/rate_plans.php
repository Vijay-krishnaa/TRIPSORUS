<?php
require_once '../db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$adminId = $_SESSION['user_id'];

// Get properties for dropdown
$propertiesQuery = $pdo->prepare("
    SELECT id, name 
    FROM properties 
    WHERE admin_id = :admin_id 
    ORDER BY name
");
$propertiesQuery->execute([':admin_id' => $adminId]);
$properties = $propertiesQuery->fetchAll(PDO::FETCH_ASSOC);

$propertyId = $_GET['property_id'] ?? ($properties[0]['id'] ?? null);

// Get room types for selected property
$roomTypes = [];
if ($propertyId) {
    $roomTypesQuery = $pdo->prepare("
        SELECT id, name 
        FROM room_types 
        WHERE property_id = :property_id 
        ORDER BY name
    ");
    $roomTypesQuery->execute([':property_id' => $propertyId]);
    $roomTypes = $roomTypesQuery->fetchAll(PDO::FETCH_ASSOC);
}

$roomTypeId = $_GET['room_type_id'] ?? ($roomTypes[0]['id'] ?? null);

// Get rate plans for selected room type
$ratePlans = [];
if ($roomTypeId) {
    $ratePlansQuery = $pdo->prepare("
        SELECT * FROM rate_plans 
        WHERE room_type_id = :room_type_id 
        ORDER BY created_at DESC
    ");
    $ratePlansQuery->execute([':room_type_id' => $roomTypeId]);
    $ratePlans = $ratePlansQuery->fetchAll(PDO::FETCH_ASSOC);
}

// Get room type details for stats
$roomTypeDetails = null;
if ($roomTypeId) {
    $roomTypeQuery = $pdo->prepare("
        SELECT * FROM room_types 
        WHERE id = :room_type_id
    ");
    $roomTypeQuery->execute([':room_type_id' => $roomTypeId]);
    $roomTypeDetails = $roomTypeQuery->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Room Rates & Inventory - TRIPSORUS Admin</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
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
        <h2>Room Rates & Inventory Management</h2>
        <div>
          <a href="../properties.php" class="btn btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-2"></i> Back to Properties
          </a>
          <a href="seasonal_rates.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>" class="btn btn-primary">
            <i class="fas fa-calendar-alt me-2"></i> Manage Seasons
          </a>
        </div>
      </div>

      <!-- Property Selection -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <label for="propertySelect" class="form-label">Select Property</label>
              <select class="form-select" id="propertySelect">
                <option value="">-- Select Property --</option>
                <?php foreach ($properties as $property): ?>
                <option value="<?= $property['id'] ?>" <?= $propertyId == $property['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($property['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="roomTypeSelect" class="form-label">Select Room Type</label>
              <select class="form-select" id="roomTypeSelect" <?= empty($roomTypes) ? 'disabled' : '' ?>>
                <option value="">-- Select Room Type --</option>
                <?php foreach ($roomTypes as $roomType): ?>
                <option value="<?= $roomType['id'] ?>" <?= $roomTypeId == $roomType['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($roomType['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <ul class="nav nav-tabs mb-4" id="ratesInventoryTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <a class="nav-link active rpi" href="rate_plans.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>">Rate Plans</a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link rpi" href="inventory_calendar.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>">Inventory Calendar</a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link rpi" href="seasonal_rates.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>">Seasonal Rates</a>
        </li>
      </ul>

      <div class="row">
        <div class="col-md-8">
          <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Meal Plan Rates</h5>
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addRatePlanModal">
                <i class="fas fa-plus me-1"></i> Add Plan
              </button>
            </div>
            <div class="card-body" id="ratePlansContainer">
              <?php if (empty($ratePlans)): ?>
                <p class="text-center text-muted">No rate plans found for this room type</p>
              <?php else: ?>
                <?php foreach ($ratePlans as $plan): ?>
                <div class="rate-plan-card mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><?= htmlspecialchars($plan['name']) ?></h6>
                    <div>
                      <button class="btn btn-sm btn-outline-secondary me-2 edit-plan" data-id="<?= $plan['id'] ?>">Edit</button>
                      <button class="btn btn-sm btn-outline-danger delete-plan" data-id="<?= $plan['id'] ?>">Delete</button>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4">
                      <label class="form-label">Base Rate (₹)</label>
                      <input type="number" class="form-control" value="<?= $plan['base_rate'] ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Extra Adult (₹)</label>
                      <input type="number" class="form-control" value="<?= $plan['extra_adult_rate'] ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Extra Child (₹)</label>
                      <input type="number" class="form-control" value="<?= $plan['extra_child_rate'] ?>" readonly>
                    </div>
                  </div>
                  <div class="mt-3">
                    <label class="form-label">Inventory Allocation</label>
                    <input type="number" class="form-control" value="<?= $plan['inventory_allocation'] ?>" style="max-width: 150px;" readonly>
                    <small class="text-muted">Number of rooms allocated to this rate plan</small>
                  </div>
                  <?php if (!empty($plan['description'])): ?>
                  <div class="mt-3">
                    <label class="form-label">Description</label>
                    <p class="form-control-static"><?= htmlspecialchars($plan['description']) ?></p>
                  </div>
                  <?php endif; ?>
                </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-header bg-white">
              <h5 class="mb-0">Quick Stats</h5>
            </div>
            <div class="card-body">
              <?php if ($roomTypeDetails): ?>
              <div class="mb-4">
                <h6>Total Inventory</h6>
                <?php
                $allocated = array_sum(array_column($ratePlans, 'inventory_allocation'));
                $totalRooms = $roomTypeDetails['quantity'];
                $percentage = $totalRooms > 0 ? round(($allocated / $totalRooms) * 100) : 0;
                ?>
                <div class="progress">
                  <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percentage ?>%">
                    <?= $allocated ?> Rooms
                  </div>
                </div>
                <small class="text-muted">Out of <?= $totalRooms ?> total rooms</small>
              </div>
              <div class="mb-4">
                <h6>Allocation Summary</h6>
                <ul class="list-group list-group-flush">
                  <?php foreach ($ratePlans as $plan): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <?= htmlspecialchars($plan['name']) ?>
                    <span class="badge bg-primary rounded-pill"><?= $plan['inventory_allocation'] ?></span>
                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <?php else: ?>
              <p class="text-muted">Select a room type to see stats</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Rate Plan Modal -->
  <div class="modal fade" id="addRatePlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Rate Plan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="ratePlanForm">
            <input type="hidden" id="roomTypeId" value="<?= $roomTypeId ?>">
            <div class="mb-3">
              <label class="form-label">Plan Name*</label>
              <input type="text" class="form-control" id="planName" placeholder="e.g., Early Bird Special" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Meal Plan*</label>
              <select class="form-select" id="mealPlan" required>
                <option value="">-- Select Meal Plan --</option>
                <option value="room_only">Room Only (No Meals)</option>
                <option value="breakfast">Bed & Breakfast</option>
                <option value="half_board">Half Board (Breakfast + Dinner)</option>
                <option value="full_board">Full Board (All Meals)</option>
              </select>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Base Rate (₹)*</label>
                <input type="number" class="form-control" id="baseRate" required step="0.01">
              </div>
              <div class="col-md-6">
                <label class="form-label">Inventory Allocation*</label>
                <input type="number" class="form-control" id="inventoryAllocation" required>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Extra Adult (₹)</label>
                <input type="number" class="form-control" id="extraAdultRate" value="0" step="0.01">
              </div>
              <div class="col-md-6">
                <label class="form-label">Extra Child (₹)</label>
                <input type="number" class="form-control" id="extraChildRate" value="0" step="0.01">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Plan Description</label>
              <textarea class="form-control" id="planDescription" rows="3"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveRatePlanBtn">Save Rate Plan</button>
        </div>
     
      </div>
    </div>
  </div>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Handle property selection change
      document.getElementById('propertySelect').addEventListener('change', function() {
        const propertyId = this.value;
        window.location.href = `rate_plans.php?property_id=${propertyId}`;
      });

      // Handle room type selection change
      document.getElementById('roomTypeSelect').addEventListener('change', function() {
        const roomTypeId = this.value;
        const propertyId = document.getElementById('propertySelect').value;
        window.location.href = `rate_plans.php?property_id=${propertyId}&room_type_id=${roomTypeId}`;
      });

      // Save rate plan
      document.getElementById('saveRatePlanBtn').addEventListener('click', function() {
        const form = document.getElementById('ratePlanForm');
        
        if (!form.checkValidity()) {
          form.classList.add('was-validated');
          return;
        }

        const formData = new FormData();
        formData.append('room_type_id', document.getElementById('roomTypeId').value);
        formData.append('name', document.getElementById('planName').value);
        formData.append('meal_plan', document.getElementById('mealPlan').value);
        formData.append('base_rate', document.getElementById('baseRate').value);
        formData.append('extra_adult_rate', document.getElementById('extraAdultRate').value);
        formData.append('extra_child_rate', document.getElementById('extraChildRate').value);
        formData.append('inventory_allocation', document.getElementById('inventoryAllocation').value);
        formData.append('description', document.getElementById('planDescription').value);

        fetch('api/save_rate_plan.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Rate plan saved successfully!');
            window.location.reload();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while saving the rate plan');
        });
      });
    });
  </script>
</body>
</html>