<?php
require_once '../db.php';
session_start();

// Check if user is logged in and is admin
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

$roomTypeId = $_GET['room_type_id'] ?? (count($roomTypes) > 0 ? $roomTypes[0]['id'] : null);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $roomTypeId = $_POST['room_type_id'];
    $mealType = $_POST['meal_type'];
    $singleRate = $_POST['single_rate'];
    $doubleRate = $_POST['double_rate'];

    // Validate dates
    if (strtotime($startDate) > strtotime($endDate)) {
        $error = "End date must be after start date";
    } else {
        // Generate dates between start and end
        $currentDate = $startDate;
        $datesToUpdate = [];
        
        while (strtotime($currentDate) <= strtotime($endDate)) {
            $datesToUpdate[] = $currentDate;
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }
        
        // Prepare SQL statement
        $successCount = 0;

        foreach ($datesToUpdate as $date) {
            $stmt = $pdo->prepare("
                INSERT INTO room_inventory 
                    (date, room_type_id, meal_type, single_rate, double_rate, property_id)
                VALUES 
                    (:date, :room_type_id, :meal_type, :single_rate, :double_rate, :property_id)
                ON DUPLICATE KEY UPDATE 
                    single_rate = VALUES(single_rate), 
                    double_rate = VALUES(double_rate)
            ");

            $stmt->execute([
                ':date' => $date,
                ':room_type_id' => $roomTypeId,
                ':meal_type' => $mealType,
                ':single_rate' => $singleRate,
                ':double_rate' => $doubleRate,
                ':property_id' => $propertyId
            ]);

            $successCount++;
        }

        $success = "Successfully updated rates for $successCount dates";
    }
}

// Define meal types
$mealTypes = [
    'room_only' => 'Room Only',
    'with_breakfast' => 'With Breakfast',
    'breakfast_lunch_dinner' => 'Breakfast + Lunch/Dinner'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bulk Rate Update - TRIPSORUS Admin</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles/style.css">
  <style>
    .card {
      box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .form-section {
      background-color: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
  </style>
</head>

<body>
  <!-- Sidebar Navigation -->
  <?php include 'sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Bulk Rate Update</h2>
        <div>
          <a href="../properties.php" class="btn btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-2"></i> Back to Properties
          </a>
          <a href="inventory_calendar.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>"
            class="btn btn-outline-primary me-2">
            <i class="fas fa-calendar me-2"></i> Inventory Calendar
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
                <option value="<?= $property['id'] ?>" <?=$propertyId==$property['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($property['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <?php if (isset($success)): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <?php if (isset($error)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header bg-white">
          <h5 class="mb-0">Bulk Rate Update Form</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="">
            <div class="row">
              <div class="col-md-6">
                <div class="form-section">
                  <h6>Date Range</h6>
                  <div class="mb-3">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="startDate" name="start_date" required
                      value="<?= $_POST['start_date'] ?? date('Y-m-d') ?>">
                  </div>
                  <div class="mb-3">
                    <label for="endDate" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="endDate" name="end_date" required
                      value="<?= $_POST['end_date'] ?? date('Y-m-d', strtotime('+7 days')) ?>">
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-section">
                  <h6>Room & Meal Type</h6>
                  <div class="mb-3">
                    <label for="roomTypeId" class="form-label">Room Type</label>
                    <select class="form-select" id="roomTypeId" name="room_type_id" required>
                      <option value="">-- Select Room Type --</option>
                      <?php foreach ($roomTypes as $roomType): ?>
                      <option value="<?= $roomType['id'] ?>" <?=($roomTypeId==$roomType['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($roomType['name']) ?>
                      </option>
                      <?php endforeach; ?>
                    </select>

                  </div>
                  <div class="mb-3">
                    <label for="mealType" class="form-label">Meal Type</label>
                    <select class="form-select" id="mealType" name="meal_type" required>
                      <option value="">-- Select Meal Type --</option>
                      <?php foreach ($mealTypes as $key => $name): ?>
                      <option value="<?= $key ?>" <?=($_POST['meal_type'] ?? '' )==$key ? 'selected' : '' ?>>
                        <?= $name ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="row mt-4">
              <div class="col-md-6">
                <div class="form-section">
                  <h6>Rates (INR)</h6>
                  <div class="mb-3">
                    <label for="singleRate" class="form-label">Single Person Rate</label>
                    <input type="number" class="form-control" id="singleRate" name="single_rate" min="0" step="1"
                      required value="<?= $_POST['single_rate'] ?? '' ?>">
                  </div>
                  <div class="mb-3">
                    <label for="doubleRate" class="form-label">Double Person Rate</label>
                    <input type="number" class="form-control" id="doubleRate" name="double_rate" min="0" step="1"
                      required value="<?= $_POST['double_rate'] ?? '' ?>">
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-section">
                  <h6>Update Options</h6>
                  <div class="mb-3">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="updateExisting" name="update_existing"
                        value="1" <?=($_POST['update_existing'] ?? 0) ? 'checked' : '' ?>>
                      <label class="form-check-label" for="updateExisting">
                        Update existing records only (skip dates without existing inventory)
                      </label>
                    </div>
                  </div>
                  <div class="mb-3">
                    <div class="alert alert-info">
                      <small>
                        <i class="fas fa-info-circle me-1"></i>
                        This will update rates for all dates in the selected range.
                        If "Update existing records only" is checked, only dates with existing inventory will be
                        updated.
                      </small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-bolt me-2"></i> Update Rates in Bulk
              </button>
              <button type="reset" class="btn btn-outline-secondary ms-2">Reset Form</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Handle property selection change
      document.getElementById('propertySelect').addEventListener('change', function () {
        const propertyId = this.value;
        window.location.href = `bulk_update.php?property_id=${propertyId}`;
      });

      // Set end date to 7 days after start date when start date changes
      document.getElementById('startDate').addEventListener('change', function () {
        const startDate = new Date(this.value);
        if (!isNaN(startDate.getTime())) {
          const endDate = new Date(startDate);
          endDate.setDate(startDate.getDate() + 7);
          document.getElementById('endDate').value = endDate.toISOString().split('T')[0];
        }
      });

      // Calculate double rate as 20% more than single rate by default
      document.getElementById('singleRate').addEventListener('input', function () {
        const singleRate = parseFloat(this.value);
        if (!isNaN(singleRate) && singleRate > 0) {
          const doubleRateInput = document.getElementById('doubleRate');
          if (!doubleRateInput.value) {
            doubleRateInput.value = Math.round(singleRate * 1.2);
          }
        }
      });
    });
  </script>
</body>

</html>