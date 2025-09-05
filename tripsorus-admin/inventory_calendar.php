<?php
require_once '../db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$adminId = $_SESSION['user_id'];
$propertiesQuery = $pdo->prepare("
    SELECT id, name 
    FROM properties 
    WHERE admin_id = :admin_id 
    ORDER BY name
");
$propertiesQuery->execute([':admin_id' => $adminId]);
$properties = $propertiesQuery->fetchAll(PDO::FETCH_ASSOC);
$propertyId = $_GET['property_id'] ?? ($properties[0]['id'] ?? null);
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
$roomTypeDetails = null;
if ($roomTypeId) {
    $roomTypeQuery = $pdo->prepare("
        SELECT * FROM room_types 
        WHERE id = :room_type_id
    ");
    $roomTypeQuery->execute([':room_type_id' => $roomTypeId]);
    $roomTypeDetails = $roomTypeQuery->fetch(PDO::FETCH_ASSOC);
}

$startDate = $_GET['start_date'] ?? date('Y-m-d'); 
$endDate = $_GET['end_date'] ?? date('Y-m-d', strtotime('+6 days')); 
$inventoryData = [];
if ($roomTypeId) {
    $inventoryQuery = $pdo->prepare("
        SELECT * FROM room_inventory 
        WHERE room_type_id = :room_type_id 
        AND date BETWEEN :start_date AND :end_date
        ORDER BY date
    ");
    $inventoryQuery->execute([
        ':room_type_id' => $roomTypeId,
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ]);
    $inventoryData = $inventoryQuery->fetchAll(PDO::FETCH_ASSOC);
}

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

$mealTypes = [
    'room_only' => 'Room Only',
    'with_breakfast' => 'With Breakfast',
    'breakfast_lunch_dinner' => 'Breakfast + Lunch/Dinner'
];
$allRoomTypes = [];
if ($propertyId) {
    $roomTypesQuery = $pdo->prepare("
        SELECT id, name, price, quantity 
        FROM room_types 
        WHERE property_id = :property_id 
        ORDER BY name
    ");
    $roomTypesQuery->execute([':property_id' => $propertyId]);
    $dbRoomTypes = $roomTypesQuery->fetchAll(PDO::FETCH_ASSOC);

    foreach ($dbRoomTypes as $rt) {
        $allRoomTypes[$rt['id']] = [
            'id' => $rt['id'],
            'name' => $rt['name'],
            'single_price' => $rt['price'],                 
            'double_price' => $rt['price'] + 500,         
            'quantity' => $rt['quantity']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory Calendar - TRIPSORUS Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="icon" href="../images/favicon.ico" type="image/ico" />
  <link rel="stylesheet" href="styles/style.css">
  <style>
  .room-type-section {
    margin-bottom: 30px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    background-color: #f9f9f9;
  }

  .room-type-header {
    background-color: #e9ecef;
    padding: 10px 15px;
    border-radius: 6px;
    margin-bottom: 15px;
  }

  .section-divider {
    height: 1px;
    background-color: #ddd;
    margin: 20px 0;
  }

  .inventory-table {
    width: 100%;
    border-collapse: collapse;
  }

  .inventory-table th,
  .inventory-table td {
    border: 1px solid #dee2e6;
    padding: 8px;
    text-align: center;
  }

  .inventory-table th {
    background-color: #f8f9fa;
    font-weight: 600;
  }

  .inventory-table td[contenteditable="true"] {
    background-color: #fff;
    min-width: 80px;
  }

  .inventory-table td[contenteditable="true"]:focus {
    outline: 2px solid #0d6efd;
    background-color: #f0f7ff;
  }

  .availability-cell {
    font-weight: bold;
    cursor: pointer;
  }

  .availability-cell.available {
    background-color: #d4edda;
    color: #155724;
  }

  .availability-cell.not-available {
    background-color: #f8d7da;
    color: #721c24;
  }

  .rate-type-header {
    background-color: #f0f3f5;
    font-weight: bold;
  }

  .room-type-filter {
    margin-bottom: 20px;
  }

  .hidden {
    display: none;
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
        <h2>Room Rates & Inventory Management</h2>
        <div>
          <a href="../properties.php" class="btn btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-2"></i> Back to Properties
          </a>
          <a href="rate_plans.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>"
            class="btn btn-outline-primary me-2">
            <i class="fas fa-money-bill me-2"></i> Rate Plans
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
            <div class="col-md-6">
              <label for="roomTypeSelect" class="form-label">Select Room Type</label>
              <select class="form-select" id="roomTypeSelect" <?=empty($roomTypes) ? 'disabled' : '' ?>>
                <option value="">-- Select Room Type --</option>
                <?php foreach ($roomTypes as $roomType): ?>
                <option value="<?= $roomType['id'] ?>" <?=$roomTypeId==$roomType['id'] ? 'selected' : '' ?>>
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
          <a class="nav-link rpi "
            href="rate_plans.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>">Rate Plans</a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link active rpi"
            href="inventory_calendar.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>">Inventory
            Calendar</a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link rpi"
            href="seasonal_rates.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>">Seasonal Rates</a>
        </li>
      </ul>

      <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Inventory Calendar:
            <?= date('M j', strtotime($startDate)) ?> -
            <?= date('M j, Y', strtotime($endDate)) ?>
          </h5>
          <div>
            <a href="inventory_calendar.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>&start_date=<?= date('Y-m-d', strtotime($startDate . ' -7 days')) ?>&end_date=<?= date('Y-m-d', strtotime($endDate . ' -7 days')) ?>"
              class="btn btn-sm btn-outline-secondary me-2">
              <i class="fas fa-chevron-left me-1"></i> Prev
            </a>
            <a href="inventory_calendar.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>&start_date=<?= date('Y-m-d') ?>&end_date=<?= date('Y-m-d', strtotime('sunday this week')) ?>"
              class="btn btn-sm btn-outline-secondary me-2">
              This Week
            </a>
            <a href="inventory_calendar.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>&start_date=<?= date('Y-m-d', strtotime($startDate . ' +7 days')) ?>&end_date=<?= date('Y-m-d', strtotime($endDate . ' +7 days')) ?>"
              class="btn btn-sm btn-outline-secondary me-2">
              Next <i class="fas fa-chevron-right ms-1"></i>
            </a>
            <button class="btn btn-sm btn-success" id="saveInventoryBtn">
              <i class="fas fa-save me-1"></i> Save All
            </button>
          </div>
        </div>
        <div class="card-body">
          <?php if ($roomTypeDetails): ?>
          <div class="inventory-calendar">
            <div class="calendar-header d-flex justify-content-between align-items-center mb-4">
              <div>
                <h5 class="mb-0">
                  <?= htmlspecialchars($properties[array_search($propertyId, array_column($properties, 'id'))]['name']) ?>
                </h5>
                <p class="text-muted mb-0">Manage availability and rates for all room types</p>
              </div>
            </div>

            <!-- Room Type Filter -->
            <div class="room-type-filter mb-4">
              <label for="roomTypeDisplayFilter" class="form-label">Display Room Type:</label>
              <select class="form-select" id="roomTypeDisplayFilter" style="max-width: 300px;">
                <option value="all">All Room Types</option>
                <?php foreach ($allRoomTypes as $roomType): ?>
                <option value="<?= $roomType['id'] ?>" <?=$roomTypeId==$roomType['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($roomType['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <?php foreach ($allRoomTypes as $roomTypeKey => $roomType): ?>
            <!-- Room Type Section -->
            <div class="room-type-section" id="room-type-<?= $roomTypeKey ?>"
              style="<?= $roomTypeId != $roomTypeKey ? 'display: none;' : '' ?>">
              <div class="room-type-header">
                <h5 class="mb-0">
                  <?= $roomType['name'] ?>
                </h5>
              </div>

              <?php foreach ($mealTypes as $mealKey => $mealName): ?>
              <!-- Section for each meal type -->
              <div class="mt-4">
                <h6>
                  <?= $mealName ?>
                </h6>
                <table class="inventory-table">
                  <thead>
                    <tr>
                      <th style="width: 15%;"></th>
                      <?php
                      $currentDate = $startDate;
                      while (strtotime($currentDate) <= strtotime($endDate)):
                      ?>
                      <th class="inventory-date" data-date="<?= $currentDate ?>">
                        <?= date('M j D', strtotime($currentDate)) ?>
                      </th>
                      <?php
                      $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                      endwhile;
                      ?>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><strong>Availability</strong></td>
                      <?php
                      $currentDate = $startDate;
                      while (strtotime($currentDate) <= strtotime($endDate)):
                        $inventoryForDate = array_filter($inventoryData, function($item) use ($currentDate, $mealKey, $roomTypeKey) {
                          return $item['date'] === $currentDate && $item['meal_type'] === $mealKey && $item['room_type_id'] === $roomTypeKey;
                        });
                        $inventoryForDate = !empty($inventoryForDate) ? reset($inventoryForDate) : null;
                        $isAvailable = $inventoryForDate ? $inventoryForDate['is_available'] : true;
                      ?>
                      <td class="availability-cell <?= $isAvailable ? 'available' : 'not-available' ?>"
                        data-field="available" data-meal-type="<?= $mealKey ?>" data-room-type="<?= $roomTypeKey ?>">
                        <?= $isAvailable ? '✓' : '✗' ?>
                      </td>
                      <?php
                      $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                      endwhile;
                      ?>
                    </tr>

                    <!-- Single Person Rate -->
                    <tr class="rate-type-header">
                      <td><strong>Single Person Rate (INR)</strong></td>
                      <?php
                      $currentDate = $startDate;
                      while (strtotime($currentDate) <= strtotime($endDate)):
                        $inventoryForDate = array_filter($inventoryData, function($item) use ($currentDate, $mealKey, $roomTypeKey) {
                          return $item['date'] === $currentDate 
                              && $item['meal_type'] === $mealKey 
                              && $item['room_type_id'] === $roomTypeKey;
                        });
                        $inventoryForDate = !empty($inventoryForDate) ? reset($inventoryForDate) : null;

                        // If rate exists in inventory, take it; else fallback to base
                        $rate = $inventoryForDate && !empty($inventoryForDate['single_rate']) 
                                ? $inventoryForDate['single_rate'] 
                                : $roomType['single_price'];
                      ?>
                      <td contenteditable="true" data-field="single_rate" data-date="<?= $currentDate ?>"
                        data-meal-type="<?= $mealKey ?>" data-room-type="<?= $roomTypeKey ?>">
                        <?= number_format($rate, 2) ?>
                      </td>
                      <?php
                      $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                      endwhile;
                      ?>
                    </tr>

                    <!-- Double Person Rate -->
                    <tr class="rate-type-header">
                      <td><strong>Double Person Rate (INR)</strong></td>
                      <?php
                      $currentDate = $startDate;
                      while (strtotime($currentDate) <= strtotime($endDate)):
                        $inventoryForDate = array_filter($inventoryData, function($item) use ($currentDate, $mealKey, $roomTypeKey) {
                          return $item['date'] === $currentDate 
                              && $item['meal_type'] === $mealKey 
                              && $item['room_type_id'] === $roomTypeKey;
                        });
                        $inventoryForDate = !empty($inventoryForDate) ? reset($inventoryForDate) : null;

                        // If rate exists in inventory, take it; else fallback to base
                        $rate = $inventoryForDate && !empty($inventoryForDate['double_rate']) 
                                ? $inventoryForDate['double_rate'] 
                                : $roomType['double_price'];
                      ?>
                      <td contenteditable="true" data-field="double_rate" data-date="<?= $currentDate ?>"
                        data-meal-type="<?= $mealKey ?>" data-room-type="<?= $roomTypeKey ?>">
                        <?= number_format($rate, 2) ?>
                      </td>
                      <?php
                      $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                      endwhile;
                      ?>
                    </tr>

                  </tbody>
                </table>
              </div>

              <div class="section-divider"></div>
              <?php endforeach; ?>

              <!-- Inventory Management Section (Common for all meal types) -->
              <div class="mt-3">
                <table class="inventory-table">
                  <thead>
                    <tr>
                      <th style="width: 15%;"></th>
                      <?php
                      $currentDate = $startDate;
                      while (strtotime($currentDate) <= strtotime($endDate)):
                      ?>
                      <th class="inventory-date" data-date="<?= $currentDate ?>">
                        <?= date('M j D', strtotime($currentDate)) ?>
                      </th>
                      <?php
                      $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                      endwhile;
                      ?>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><strong>Total Rooms</strong></td>
                      <?php
                      $currentDate = $startDate;
                      while (strtotime($currentDate) <= strtotime($endDate)):
                        $inventoryForDate = array_filter($inventoryData, function($item) use ($currentDate, $roomTypeKey) {
                          return $item['date'] === $currentDate && $item['meal_type'] === 'room_only' && $item['room_type_id'] === $roomTypeKey;
                        });
                        $inventoryForDate = !empty($inventoryForDate) ? reset($inventoryForDate) : null;
                        $totalRooms = $inventoryForDate ? $inventoryForDate['total_rooms'] : $roomType['quantity'];
                      ?>
                      <td contenteditable="true" data-field="total_rooms" data-date="<?= $currentDate ?>"
                        data-room-type="<?= $roomTypeKey ?>">
                        <?= $totalRooms ?>
                      </td>
                      <?php
                      $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                      endwhile;
                      ?>
                    </tr>
                    <tr>
                      <td><strong>Booked Rooms</strong></td>
                      <?php
                      $currentDate = $startDate;
                      while (strtotime($currentDate) <= strtotime($endDate)):
                        $inventoryForDate = array_filter($inventoryData, function($item) use ($currentDate, $roomTypeKey) {
                          return $item['date'] === $currentDate && $item['meal_type'] === 'room_only' && $item['room_type_id'] === $roomTypeKey;
                        });
                        $inventoryForDate = !empty($inventoryForDate) ? reset($inventoryForDate) : null;
                        $bookedRooms = $inventoryForDate ? $inventoryForDate['booked_rooms'] : 0;
                      ?>
                      <td contenteditable="true" data-field="booked_rooms" data-date="<?= $currentDate ?>"
                        data-room-type="<?= $roomTypeKey ?>">
                        <?= $bookedRooms ?>
                      </td>
                      <?php
                      $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                      endwhile;
                      ?>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <?php endforeach; ?>

            <!-- Rate Plans Sections -->
            <?php foreach ($ratePlans as $plan): ?>
            <div class="section-divider"></div>
            <div class="room-type-header d-flex justify-content-between align-items-center">
              <div>
                <?= htmlspecialchars($plan['name']) ?>
              </div>
            </div>
            <div class="mt-3">
              <table class="inventory-table">
                <thead>
                  <tr>
                    <th style="width: 15%;"></th>
                    <?php
                    $currentDate = $startDate;
                    while (strtotime($currentDate) <= strtotime($endDate)):
                    ?>
                    <th class="inventory-date" data-date="<?= $currentDate ?>">
                      <?= date('M j D', strtotime($currentDate)) ?>
                    </th>
                    <?php
                    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                    endwhile;
                    ?>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><strong>Availability</strong></td>
                    <?php
                    $currentDate = $startDate;
                    while (strtotime($currentDate) <= strtotime($endDate)):
                      // For simplicity, we'll use the same availability as base
                      $inventoryForDate = array_filter($inventoryData, function($item) use ($currentDate) {
                        return $item['date'] === $currentDate && $item['meal_type'] === 'room_only';
                      });
                      $inventoryForDate = !empty($inventoryForDate) ? reset($inventoryForDate) : null;
                      $isAvailable = $inventoryForDate ? $inventoryForDate['is_available'] : true;
                    ?>
                    <td class="availability-cell <?= $isAvailable ? 'available' : 'not-available' ?>"
                      data-field="available">
                      <?= $isAvailable ? '✓' : '✗' ?>
                    </td>
                    <?php
                    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                    endwhile;
                    ?>
                  </tr>
                  <tr>
                    <td><strong>Rate (INR)</strong></td>
                    <?php
                    $currentDate = $startDate;
                    while (strtotime($currentDate) <= strtotime($endDate)):
                      // Calculate rate with potential seasonal adjustments
                      $baseRate = $plan['base_rate'];
                      // In a real application, you would apply seasonal adjustments here
                      $adjustedRate = $baseRate;
                    ?>
                    <td contenteditable="true" data-field="rate_<?= $plan['id'] ?>" data-date="<?= $currentDate ?>">
                      <?= $adjustedRate ?>
                    </td>
                    <?php
                    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                    endwhile;
                    ?>
                  </tr>
                </tbody>
              </table>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <p class="text-center text-muted">Please select a property and room type to view inventory</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Replace the entire JavaScript section at the bottom of your file with this corrected version -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Handle property selection change
    document.getElementById('propertySelect').addEventListener('change', function() {
      const propertyId = this.value;
      window.location.href = `inventory_calendar.php?property_id=${propertyId}`;
    });

    // Handle room type selection change
    document.getElementById('roomTypeSelect').addEventListener('change', function() {
      const roomTypeId = this.value;
      const propertyId = document.getElementById('propertySelect').value;
      window.location.href = `inventory_calendar.php?property_id=${propertyId}&room_type_id=${roomTypeId}`;
    });

    // Handle room type display filter
    document.getElementById('roomTypeDisplayFilter').addEventListener('change', function() {
      const selectedRoomTypeId = this.value;

      // Show all room types if "All" is selected
      if (selectedRoomTypeId === 'all') {
        document.querySelectorAll('.room-type-section').forEach(section => {
          section.style.display = 'block';
        });
      } else {
        // Hide all room types
        document.querySelectorAll('.room-type-section').forEach(section => {
          section.style.display = 'none';
        });

        // Show only the selected room type
        const selectedSection = document.getElementById(`room-type-${selectedRoomTypeId}`);
        if (selectedSection) {
          selectedSection.style.display = 'block';
        }
      }
    });

    // Toggle availability on click
    document.querySelectorAll('.availability-cell').forEach(cell => {
      cell.addEventListener('click', function() {
        this.classList.toggle('available');
        this.classList.toggle('not-available');
        this.textContent = this.classList.contains('available') ? '✓' : '✗';
      });
    });

    // Helper function to clean numbers
    function cleanNumber(txt) {
      if (!txt) return null;
      return parseFloat(String(txt).replace(/[₹,\s]/g, '')) || null;
    }

    // Save inventory
    // Replace the save inventory button event listener with this updated version
    document.getElementById('saveInventoryBtn').addEventListener('click', function() {
      const updates = [];
      const propertyId = <?= $propertyId ?: 'null' ?>;
      const selectedRoomTypeId = document.getElementById('roomTypeDisplayFilter').value;

      if (!propertyId) {
        alert('Please select a property first');
        return;
      }

      // Collect all editable cells
      const editableCells = document.querySelectorAll('td[contenteditable="true"]');

      editableCells.forEach(cell => {
        const date = cell.getAttribute('data-date');
        const field = cell.getAttribute('data-field');
        const mealType = cell.getAttribute('data-meal-type');
        const roomTypeId = cell.getAttribute('data-room-type');
        const rawValue = cell.textContent.trim();

        // Only collect data for the selected room type (or all if "all" is selected)
        if (selectedRoomTypeId === 'all' || roomTypeId === selectedRoomTypeId) {
          let value;

          if (field === 'single_rate' || field === 'double_rate') {
            value = cleanNumber(rawValue);
          } else {
            value = parseInt(rawValue) || 0;
          }

          updates.push({
            date: date,
            field: field,
            value: value,
            meal_type: mealType,
            room_type_id: roomTypeId,
            property_id: propertyId
          });
        }
      });

      // Collect availability data
      const availabilityCells = document.querySelectorAll('.availability-cell[data-field="available"]');
      availabilityCells.forEach(cell => {
        const date = cell.getAttribute('data-date');
        const mealType = cell.getAttribute('data-meal-type');
        const roomTypeId = cell.getAttribute('data-room-type');
        const isAvailable = cell.classList.contains('available');

        if (selectedRoomTypeId === 'all' || roomTypeId === selectedRoomTypeId) {
          updates.push({
            date: date,
            field: 'is_available',
            value: isAvailable ? 1 : 0,
            meal_type: mealType,
            room_type_id: roomTypeId,
            property_id: propertyId
          });
        }
      });

      // Send updates to server
      fetch('api/save_inventory.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            updates: updates
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            alert('Inventory saved successfully!');
            window.location.reload();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while saving inventory');
        });
    });
  });
  </script>
</body>

</html>