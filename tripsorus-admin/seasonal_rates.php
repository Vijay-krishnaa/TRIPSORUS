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

$roomTypeId = $_GET['room_type_id'] ?? ($roomTypes[0]['id'] ?? null);

// Get seasons
$seasonsQuery = $pdo->prepare("
    SELECT * FROM seasons 
    ORDER BY start_date DESC
");
$seasonsQuery->execute();
$seasons = $seasonsQuery->fetchAll(PDO::FETCH_ASSOC);

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

// Get seasonal rates
$seasonalRates = [];
if (!empty($ratePlans)) {
    $ratePlanIds = array_column($ratePlans, 'id');
    $placeholders = implode(',', array_fill(0, count($ratePlanIds), '?'));
    
    $seasonalRatesQuery = $pdo->prepare("
        SELECT sr.*, s.name as season_name, s.start_date, s.end_date, 
               s.adjustment_type, s.adjustment_value
        FROM seasonal_rates sr
        JOIN seasons s ON sr.season_id = s.id
        WHERE sr.rate_plan_id IN ($placeholders)
        ORDER BY s.start_date DESC
    ");
    $seasonalRatesQuery->execute($ratePlanIds);
    $seasonalRates = $seasonalRatesQuery->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Seasonal Rates - TRIPSORUS Admin</title>
  <link rel="icon" href="../images/favicon.ico" type="image/ico" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
          <a href="rate_plans.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>" class="btn btn-outline-primary me-2">
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
          <a class="nav-link rpi" href="rate_plans.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>">Rate Plans</a>
        </li>
        <li class="nav-item " role="presentation">
          <a class="nav-link rpi" href="inventory_calendar.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>">Inventory Calendar</a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link rpi active" href="seasonal_rates.php?property_id=<?= $propertyId ?>&room_type_id=<?= $roomTypeId ?>">Seasonal Rates</a>
        </li>
      </ul>

      <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Seasonal Rates Management</h5>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addSeasonModal">
            <i class="fas fa-plus me-1"></i> Add Season
          </button>
        </div>
        <div class="card-body">
          <?php if (empty($seasons)): ?>
            <p class="text-center text-muted">No seasons found. Create your first season to get started.</p>
          <?php else: ?>
            <?php foreach ($seasons as $season): ?>
            <div class="season-card">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><?= htmlspecialchars($season['name']) ?> (<?= date('M j, Y', strtotime($season['start_date'])) ?> - <?= date('M j, Y', strtotime($season['end_date'])) ?>)</h5>
                <div>
                  <button class="btn btn-sm btn-outline-secondary me-2 edit-season" data-id="<?= $season['id'] ?>">Edit</button>
                  <button class="btn btn-sm btn-outline-danger delete-season" data-id="<?= $season['id'] ?>">Delete</button>
                </div>
              </div>
              
              <div class="mb-3">
                <strong>Type:</strong> <?= ucfirst($season['season_type']) ?> season
                <strong class="ms-3">Adjustment:</strong> 
                <?= $season['adjustment_type'] === 'increase' ? '+' : '-' ?>
                <?= $season['adjustment_value'] ?>%
              </div>
              
              <?php if (!empty($season['description'])): ?>
              <div class="mb-3">
                <strong>Description:</strong> <?= htmlspecialchars($season['description']) ?>
              </div>
              <?php endif; ?>
              
              <?php if (!empty($ratePlans)): ?>
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th>Rate Plan</th>
                      <th>Base Rate</th>
                      <th>Extra Adult</th>
                      <th>Extra Child</th>
                      <th><?= $season['adjustment_type'] === 'increase' ? 'Markup %' : 'Discount %' ?></th>
                      <th>Final Rate</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($ratePlans as $plan): 
                      // Find seasonal rate for this plan and season
                      $seasonalRate = null;
                      foreach ($seasonalRates as $sr) {
                        if ($sr['rate_plan_id'] == $plan['id'] && $sr['season_id'] == $season['id']) {
                          $seasonalRate = $sr;
                          break;
                        }
                      }
                      
                      // Calculate adjusted rate
                      $baseRate = $plan['base_rate'];
                      if ($seasonalRate) {
                        $adjustedRate = $seasonalRate['adjusted_rate'];
                      } else {
                        if ($season['adjustment_type'] === 'increase') {
                          $adjustedRate = $baseRate * (1 + ($season['adjustment_value'] / 100));
                        } else {
                          $adjustedRate = $baseRate * (1 - ($season['adjustment_value'] / 100));
                        }
                      }
                    ?>
                    <tr>
                      <td><?= htmlspecialchars($plan['name']) ?></td>
                      <td>₹<?= number_format($baseRate, 2) ?></td>
                      <td>₹<?= number_format($plan['extra_adult_rate'], 2) ?></td>
                      <td>₹<?= number_format($plan['extra_child_rate'], 2) ?></td>
                      <td>
                        <?php if ($seasonalRate): ?>
                        <input type="number" class="form-control form-control-sm" value="<?= $season['adjustment_value'] ?>" readonly>
                        <?php else: ?>
                        <span class="text-muted">Not set</span>
                        <?php endif; ?>
                      </td>
                      <td>₹<?= number_format($adjustedRate, 2) ?></td>
                      <td>
                        <?php if ($seasonalRate): ?>
                        <button class="btn btn-sm btn-outline-danger remove-seasonal-rate" data-id="<?= $seasonalRate['id'] ?>">Remove</button>
                        <?php else: ?>
                        <button class="btn btn-sm btn-outline-primary add-seasonal-rate" data-season-id="<?= $season['id'] ?>" data-rate-plan-id="<?= $plan['id'] ?>">Apply</button>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <?php else: ?>
              <p class="text-muted">No rate plans found for this room type</p>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Season Modal -->
  <div class="modal fade" id="addSeasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Season</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="seasonForm">
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Season Name*</label>
                <input type="text" class="form-control" id="seasonName" placeholder="e.g., Peak Season" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Season Type*</label>
                <select class="form-select" id="seasonType" required>
                  <option value="">-- Select Type --</option>
                  <option value="peak">Peak Season (Higher Rates)</option>
                  <option value="off">Off Season (Discounts)</option>
                  <option value="special">Special Event</option>
                </select>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Start Date*</label>
                <input type="date" class="form-control" id="seasonStartDate" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">End Date*</label>
                <input type="date" class="form-control" id="seasonEndDate" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Rate Adjustment*</label>
              <div class="input-group">
                <input type="number" class="form-control" id="adjustmentValue" placeholder="e.g., 25 for 25% increase" required step="0.01" min="0" max="100">
                <span class="input-group-text">%</span>
                <select class="form-select" id="adjustmentType" style="max-width: 150px;" required>
                  <option value="increase">Increase</option>
                  <option value="decrease">Decrease</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Apply To</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="applyAll" checked>
                <label class="form-check-label" for="applyAll">All Rate Plans</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="applyRoomOnly">
                <label class="form-check-label" for="applyRoomOnly">Room Only</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="applyBreakfast">
                <label class="form-check-label" for="applyBreakfast">Bed & Breakfast</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="applyFullBoard">
                <label class="form-check-label" for="applyFullBoard">Full Board</label>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Season Description</label>
              <textarea class="form-control" id="seasonDescription" rows="3" placeholder="Optional description about this season"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveSeasonBtn">Create Season</button>
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
        window.location.href = `seasonal_rates.php?property_id=${propertyId}`;
      });

      // Handle room type selection change
      document.getElementById('roomTypeSelect').addEventListener('change', function() {
        const roomTypeId = this.value;
        const propertyId = document.getElementById('propertySelect').value;
        window.location.href = `seasonal_rates.php?property_id=${propertyId}&room_type_id=${roomTypeId}`;
      });

      // Save season
      document.getElementById('saveSeasonBtn').addEventListener('click', function() {
        const form = document.getElementById('seasonForm');
        
        if (!form.checkValidity()) {
          form.classList.add('was-validated');
          return;
        }

        const formData = new FormData();
        formData.append('name', document.getElementById('seasonName').value);
        formData.append('season_type', document.getElementById('seasonType').value);
        formData.append('start_date', document.getElementById('seasonStartDate').value);
        formData.append('end_date', document.getElementById('seasonEndDate').value);
        formData.append('adjustment_value', document.getElementById('adjustmentValue').value);
        formData.append('adjustment_type', document.getElementById('adjustmentType').value);
        formData.append('description', document.getElementById('seasonDescription').value);

        // Get which rate plans to apply to
        const applyTo = [];
        if (document.getElementById('applyAll').checked) applyTo.push('all');
        if (document.getElementById('applyRoomOnly').checked) applyTo.push('room_only');
        if (document.getElementById('applyBreakfast').checked) applyTo.push('breakfast');
        if (document.getElementById('applyFullBoard').checked) applyTo.push('full_board');
        
        formData.append('apply_to', JSON.stringify(applyTo));

        fetch('api/save_season.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Season created successfully!');
            window.location.reload();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while creating the season');
        });
      });

      // Add seasonal rate to a plan
      document.querySelectorAll('.add-seasonal-rate').forEach(btn => {
        btn.addEventListener('click', function() {
          const seasonId = this.getAttribute('data-season-id');
          const ratePlanId = this.getAttribute('data-rate-plan-id');
          
          fetch('add_seasonal_rate.php', {
            method: 'POST',
            body: JSON.stringify({
              season_id: seasonId,
              rate_plan_id: ratePlanId
            }),
            headers: {
              'Content-Type': 'application/json'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Seasonal rate applied successfully!');
              window.location.reload();
            } else {
              alert('Error: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while applying the seasonal rate');
          });
        });
      });

      // Remove seasonal rate
      document.querySelectorAll('.remove-seasonal-rate').forEach(btn => {
        btn.addEventListener('click', function() {
          const seasonalRateId = this.getAttribute('data-id');
          
          if (!confirm('Are you sure you want to remove this seasonal rate?')) return;
          
          fetch('remove_seasonal_rate.php', {
            method: 'POST',
            body: JSON.stringify({
              seasonal_rate_id: seasonalRateId
            }),
            headers: {
              'Content-Type': 'application/json'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Seasonal rate removed successfully!');
              window.location.reload();
            } else {
              alert('Error: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing the seasonal rate');
          });
        });
      });
    });
  </script>
</body>
</html>