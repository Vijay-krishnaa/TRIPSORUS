<?php
session_start();
include 'db.php';
function getActivePromotions($pdo)
{
  $stmt = $pdo->prepare("SELECT * FROM promotions WHERE status = 'active'");
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$promotions = getActivePromotions($pdo);
function getRoomPrice($pdo, $roomTypeId, $mealType, $checkin, $checkout, $promotions)
{
  $stmt = $pdo->prepare("
        SELECT MIN(single_rate) AS price 
        FROM room_inventory 
        WHERE room_type_id = :roomTypeId
          AND meal_type = :mealType
          AND date >= :checkin
          AND date < :checkout
    ");
  $stmt->execute([
    ':roomTypeId' => $roomTypeId,
    ':mealType' => $mealType,
    ':checkin' => $checkin,
    ':checkout' => $checkout
  ]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $price = $row['price'] ?? 0;
  $checkinDate = new DateTime($checkin);
  $checkoutDate = new DateTime($checkout);
  $nights = $checkoutDate->diff($checkinDate)->days;
  $today = new DateTime();
  $daysBeforeCheckin = $checkinDate->diff($today)->days;

  $appliedPromotions = [];

  foreach ($promotions as $promo) {
    $applied = false;
    if ($promo['type'] === 'last-minute' && $daysBeforeCheckin <= $promo['days_before_checkin']) {
      $applied = true;
    }
    if ($promo['type'] === 'early-bird' && $daysBeforeCheckin >= $promo['days_before_checkin']) {
      $applied = true;
    }
    if ($promo['type'] === 'long-stay' && $nights >= $promo['min_stay_days']) {
      $applied = true;
    }
    if ($applied) {
      if ($promo['discount_type'] === 'percentage') {
        $price -= ($price * $promo['discount_value'] / 100);
      } else {
        $price -= $promo['discount_value'];
      }

      $appliedPromotions[] = $promo['discount_value'] . "% " . $promo['name'];
    }
  }
  return [
    'price' => max($price, 0),
    'applied_promotions' => $appliedPromotions
  ];
}

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 4;
$offset = ($page - 1) * $limit;
$location = $_GET['location'] ?? '';
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$rooms = isset($_GET['rooms']) ? (int) $_GET['rooms'] : 1;
$adults = isset($_GET['adults']) ? (int) $_GET['adults'] : 2;
$children = isset($_GET['children']) ? (int) $_GET['children'] : 0;


if (empty($checkin)) {
  $checkin = date('Y-m-d');
}
if (empty($checkout)) {
  $tomorrow = date('Y-m-d', strtotime('+1 day'));
  $checkout = $tomorrow;
}

$searchTerm = "%$location%";
$propertyName = null;
$propertyAddress = '';
$propertyCity = '';
$propertyCountry = '';
$propertyImage = '';
$propertyMapLink = '';
$propertyAmenities = '';

if (isset($_GET['id']) && !empty($_GET['id'])) {
  $propertyId = (int) $_GET['id'];
  $propStmt = $pdo->prepare("
    SELECT 
        name, 
        address, 
        city, 
        country,
        map_link,
        description,
        amenities,
        checkin_time,
        checkout_time,
        (SELECT pi.image_path 
         FROM property_images pi 
         WHERE pi.property_id = p.id 
           AND pi.is_main = 1 
         LIMIT 1) AS property_image
    FROM properties p
    WHERE p.id = :id
    LIMIT 1
");
  $propStmt->execute([':id' => $propertyId]);
  $propRow = $propStmt->fetch(PDO::FETCH_ASSOC);
  $propertyCheckin = !empty($propRow['checkin_time'])
    ? date("h:i A", strtotime($propRow['checkin_time']))
    : "12:00 PM";

  $propertyCheckout = !empty($propRow['checkout_time'])
    ? date("h:i A", strtotime($propRow['checkout_time']))
    : "11:00 AM";
  if ($propRow) {
    $propertyName = $propRow['name'];
    $propertyAddress = $propRow['address'];
    $propertyCity = $propRow['city'];
    $propertyCountry = $propRow['country'];
    $propertyMapLink = $propRow['map_link'];
    $propertyAmenities = $propRow['amenities'];
    $propertyImage = $propRow['property_image'];
  }
}
$amenityIcons = [
  'wifi' => 'fa-wifi',
  'air conditioning' => 'fa-snowflake',
  'parking' => 'fa-square-parking',
  'pool' => 'fa-person-swimming',
  'restaurant' => 'fa-utensils',
  'bar' => 'fa-glass-martini-alt',
  'gym' => 'fa-dumbbell',
  'spa' => 'fa-spa',
  'tv' => 'fa-tv',
  'balcony' => 'fa-building',
  'minibar' => 'fa-wine-bottle',
  'electric kettle' => 'fa-mug-hot',
  'breakfast' => 'fa-bread-slice',
  'default' => 'fa-check'
];
$propertyImages = [];
if (isset($_GET['id']) && !empty($_GET['id'])) {
  $propertyId = (int) $_GET['id'];
  $imageStmt = $pdo->prepare("
        SELECT image_path 
        FROM property_images 
        WHERE property_id = :property_id
        ORDER BY is_main DESC, id ASC
        LIMIT 5
    ");
  $imageStmt->execute([':property_id' => $propertyId]);
  $propertyImages = $imageStmt->fetchAll(PDO::FETCH_ASSOC);
}
if (count($propertyImages) < 5) {
  $defaultImages = [
    ['image_path' => 'images/river1.jpg'],
    ['image_path' => 'images/river2.jpg'],
    ['image_path' => 'images/river3.jpg'],
    ['image_path' => 'images/river4.jpg'],
    ['image_path' => 'images/river5.jpg']
  ];

  for ($i = count($propertyImages); $i < 5; $i++) {
    $propertyImages[] = $defaultImages[$i];
  }
}

$roomTypes = [];
if (isset($_GET['id']) && !empty($_GET['id'])) {
  $propertyId = (int) $_GET['id'];
  $roomStmt = $pdo->prepare("
        SELECT 
            rt.id, 
            rt.name,
                    rt.amenities,
            rt.description,
            (SELECT ri.image_path 
             FROM room_images ri 
             WHERE ri.room_type_id = rt.id 
               AND ri.is_main = 1 
             LIMIT 1) AS image
        FROM room_types rt 
        WHERE rt.property_id = :property_id
    ");
  $roomStmt->execute([':property_id' => $propertyId]);
  $roomTypes = $roomStmt->fetchAll(PDO::FETCH_ASSOC);
}

$mealTypes = [
  'Room Only' => 'room_only',
  'With Breakfast' => 'with_breakfast',
  'Breakfast+lunch/dinner' => 'breakfast_lunch_dinner'
];
$checkinDateTime = $checkin . ' ' . $propRow['checkin_time'];
$dt = new DateTime($checkinDateTime);
$dt->modify('-1 minute');
$freeCancellation = $dt->format('F j, Y g:i A');
$stmt = $pdo->prepare("
    SELECT rc.id AS category_id, rc.name AS category_name, rc.slug,
           r.id AS rule_id, r.title,
           ri.id AS item_id, ri.content
    FROM rule_categories rc
    LEFT JOIN rules r ON rc.id = r.category_id
    LEFT JOIN rule_items ri ON r.id = ri.rule_id
    ORDER BY rc.id, r.id, ri.item_order
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [];
foreach ($rows as $row) {
  $catId = $row['category_id'];
  if (!isset($data[$catId])) {
    $data[$catId] = [
      'id' => $catId,
      'name' => $row['category_name'],
      'slug' => $row['slug'],
      'rules' => []
    ];
  }

  if ($row['rule_id']) {
    if (!isset($data[$catId]['rules'][$row['rule_id']])) {
      $data[$catId]['rules'][$row['rule_id']] = [
        'id' => $row['rule_id'],
        'title' => $row['title'],
        'items' => []
      ];
    }
    if ($row['item_id']) {
      $data[$catId]['rules'][$row['rule_id']]['items'][] = $row['content'];
    }
  }
}
foreach ($data as &$cat) {
  $cat['rules'] = array_values($cat['rules']);
}
$ruleCategories = array_values($data);
function getRoomImages($pdo, $roomTypeId)
{
  $stmt = $pdo->prepare("
        SELECT image_path 
        FROM room_images 
        WHERE room_type_id = :room_type_id
        ORDER BY is_main DESC, id ASC
    ");
  $stmt->execute([':room_type_id' => $roomTypeId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
    <?php echo htmlspecialchars($propertyName); ?> - TRIPSORUS
  </title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="icon" href="images/favicon.ico" type="image/png">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="styles/style.css">
  <style>
    .property-info-section {
      max-width: 1000px;
      margin: 0 auto;
      background: white;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }


    .section-title {
      font-size: 24px;
      color: #2b6cb0;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #e2e8f0;
    }

    .property-description {
      font-size: 15px;
      line-height: 1.6;
      color: #555;
      margin-bottom: 15px;
    }

    .read-more-btn {
      color: #1a73e8;
      background: none;
      border: none;
      padding: 0;
      font-size: 14px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
    }

    .read-more-btn:hover {
      text-decoration: underline;
    }

    .hotel-rules-container {
      background: white;
      width: 117%;
      border-radius: 8px;
      padding: 10px;

      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .hotel-name {
      font-size: 20px;
      font-weight: bold;
      color: #333;
      margin-bottom: -4px;
    }

    .check-in-out {
      font-size: 14px;
      color: #666;
      margin-bottom: 3px;
    }

    .rule-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: -14px;
    }

    .rule-text {
      font-size: 15px;
      color: #333;
      line-height: 0.5rem;
    }

    .rules-categories {
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
    }

    .category-link {
      font-size: 14px;
      text-decoration: none;
      padding: 0 5px;
    }

    .category-link:hover {
      text-decoration: underline;
    }

    .view-all-btn {
      display: block;
      width: 100%;
      background: transparent;
      color: #1a73e8;
      border: 1px solid #dadce0;
      border-radius: 4px;
      padding: 10px;
      font-size: 14px;
      cursor: pointer;
      text-align: center;
      margin-top: 15px;
    }

    .view-all-btn:hover {
      background-color: #f8f9fa;
    }

    .divider {
      height: 16px;
      width: 1px;
      background-color: #dadce0;
      margin: 0 5px;
    }

    .amenities-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 12px;
      margin-top: 15px;
    }


    .amenity-item .material-icons {
      font-size: 18px;
      color: #0a55ff;
      margin-right: 10px;
    }

    .amenity-text {
      font-size: 14px;
      color: #444;
    }

    .amenities-toggle-btn {
      background: transparent;
      color: #1a73e8;
      border: 1px solid #dadce0;
      border-radius: 4px;
      padding: 8px 15px;
      font-size: 14px;
      cursor: pointer;
      text-align: center;
      display: inline-flex;
      align-items: center;
      margin-top: 15px;
    }

    .amenities-toggle-btn:hover {
      background-color: #f8f9fa;
    }

    /* Popup Styles */
    .popup-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.7);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s, visibility 0.3s;
    }

    .popup-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .popup-content {
      background: white;
      border-radius: 12px;
      width: 90%;
      max-width: 800px;
      max-height: 90vh;
      overflow-y: auto;
      padding: 30px;
      position: relative;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .popup-close {
      position: absolute;
      top: 15px;
      right: 15px;
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #666;
    }

    .popup-close:hover {
      color: #333;
    }

    .popup-title {
      font-size: 24px;
      color: #333;
      margin-bottom: 20px;
      text-align: center;
    }

    .rules-category {
      margin-bottom: 25px;
    }

    .rules-category-title {
      font-size: 18px;
      color: black;
      margin-bottom: 10px;
      padding-bottom: 5px;
      border-bottom: 1px solid #e2e8f0;
    }

    .rules-list {
      padding-left: 20px;
    }

    .rules-list li {
      margin-bottom: 8px;
      line-height: 1.5;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .property-info-section {
        padding: 20px;
      }

      .hotel-rules-container {
        background: white;
        width: 100%;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      }

      .amenities-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 10px;
      }

      .rules-categories {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }

      .divider {
        display: none;
      }

      .popup-content {
        padding: 20px;
      }
    }


    .room-options-container {
      display: flex;
      flex-direction: column;
      gap: 15px;
      margin-top: 15px;
      width: 100%;
      max-width: 100%;
    }

    .room-option:hover {
      border-color: #007bff;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      transform: translateY(-5px) scale(1.02);
      cursor: pointer;
    }

    .room-option.highlighted {
      border: 2px solid #0a55ff;
      background-color: #f8fff8;
      position: relative;
    }

    .room-option.highlighted::before {
      content: "MOST POPULAR";
      position: absolute;
      top: -10px;
      right: 15px;
      background-color: #0a55ff;
      color: white;
      padding: 3px 10px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: bold;
    }

    .option-header {
      margin-bottom: 12px;
      flex: 1 1 65%;
    }

    .option-header h4 {
      font-size: 16px;
      font-weight: 600;
      margin: 0;
      color: #333;
    }

    .free-cancellation {
      font-size: 12px;
      color: #0a55ff;
      font-weight: 500;
      margin-top: 5px;
    }

    .option-benefits {
      margin: 15px 0;
      padding-left: 0;
      list-style: none;
    }

    .option-benefits li {
      font-size: 13px;
      color: #555;
      margin-bottom: 8px;
      display: flex;
      align-items: flex-start;
      gap: 8px;
    }

    .option-benefits i {
      color: #0a55ff;
      font-size: 14px;
      margin-top: 2px;
    }

    .room-content {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      width: 100%;
      gap: 20px;
    }

    .option-price-container {
      flex: 0 0 30%;
      margin-top: 0;
      display: flex;
      flex-direction: column;
      align-items: flex-end;
    }

    .price-display {
      display: flex;
      align-items: baseline;
      gap: 10px;
      margin-bottom: 5px;
    }

    .original-price {
      font-size: 14px;
      color: #999;
      text-decoration: line-through;
    }

    .discounted-price {
      font-size: 20px;
      font-weight: 700;
      color: #333;
    }

    .taxes {
      font-size: 12px;
      color: #666;
      margin-bottom: 15px;
      text-align: right;
    }


    .select-btn:hover {
      background-color: #005999;
    }

    .bank-offer {
      font-size: 12px;
      color: #d23b38;
      margin-top: 10px;
      font-weight: 500;
      text-align: center;
    }

    .room-availability {
      font-size: 12px;
      color: #d23b38;
      margin-top: 10px;
      text-align: center;
      font-weight: 500;
    }

    .single-room-image {
      height: 168px;
      width: 250px;
      border-radius: 15px;
      object-fit: cover;
      transition: transform 0.5s ease, box-shadow 0.5s ease, filter 0.5s ease;
      will-change: transform, box-shadow;
    }

    .single-room-image:hover {
      transform: rotateX(2deg) rotateY(2deg);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
      filter: brightness(1.08) saturate(1.05);
      cursor: pointer;
    }


    @media (max-width: 900px) {
      .room-option {
        flex-direction: column;
        max-width: 100%;
        padding: 20px;
      }

      .room-content {
        flex-direction: column;
        gap: 15px;
      }

      .option-price-container {
        align-items: flex-start;
        width: 100%;
        margin-top: 15px;
      }

      .taxes {
        text-align: left;
      }

      .select-btn {
        width: 100%;
      }

      .room-option.highlighted::before {
        right: auto;
        left: 15px;
      }
    }

    @media (max-width: 500px) {
      .price-display {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
      }

      .single-room-image {
        width: 100%;
        height: auto;
        max-height: 200px;
      }
    }

    .guest-modal {
      display: none;
      position: absolute;
      top: 100%;
      left: 0;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 15px;
      z-index: 1000;
      width: 250px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin-top: 5px;
    }

    .counter-group {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .counter-group span:first-child {
      font-size: 14px;
      color: #333;
    }

    .counter-btn {
      background: #f2f2f2;
      border: 1px solid #ddd;
      border-radius: 3px;
      width: 25px;
      height: 25px;
      cursor: pointer;
      font-weight: bold;
    }

    .counter-btn:hover {
      background: #e6e6e6;
    }

    #adultsCounter,
    #childrenCounter,
    #roomsCounter {
      margin: 0 10px;
      font-weight: 600;
      min-width: 20px;
      text-align: center;
    }

    .room-header {
      margin: 10px;
      padding: 5px;
      max-width: min-content;

    }

    .material-icons.small-icon {
      font-size: 14px;
      vertical-align: middle;
    }

    .property-info-section {
      margin: 30px 0;
    }

    .info-row {
      margin-bottom: -2px;
      width: 120%;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .section-title {
      font-size: 20px;
      font-weight: 500;
      margin-bottom: 4px;
      color: #333;
    }

    .info-row p {
      font-size: 15px;
      line-height: 1.6;
      color: #555;
      margin-bottom: -18px;
    }

    .view-all-btn,
    .read-more-btn {
      display: contents;
      align-items: center;
      padding: 4px 8px;
      font-size: 13px;
      margin-left: 8px;
      border: none;
      background: none;
      color: #0a55ff;
      cursor: pointer;
      font-weight: 500;
    }


    .amenities-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 12px;
    }

    .amenity-item {
      display: flex;
      align-items: center;
      padding: 8px 10px;
      background: #f8f9fa;
      border-radius: 6px;
    }




    @media (max-width: 768px) {
      .amenities-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 10px;
      }

      .info-row {
        width: 110%;
      }
    }

    @media (max-width: 480px) {
      .amenities-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .gallery-grid {
        display: block;
      }

      .gallery-grid .gallery-main {
        width: 100%;
        margin-bottom: 0;
      }

      .gallery-grid .rest-img {
        display: none !important;
      }

    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>
  <!-- Main Content -->
  <main class="container">
    <!-- Hero Section -->
    <section class="hero">
      <?php if ($propertyName): ?>
        <h1 class="hotel-title">
          <?= htmlspecialchars($propertyName) ?>
        </h1>
      <?php endif; ?>

      <div class="hotel-location">
        <i class="fas fa-map-marker-alt"></i>
        <span>
          <?= htmlspecialchars($propertyAddress) ?>,
          <?= htmlspecialchars($propertyCity) ?>,
          <?= htmlspecialchars($propertyCountry) ?>
        </span>
      </div>
      <div class="rating-badge">
        <span class="rating-score">8.6</span>
        <span>Excellent</span>
      </div>
      <!-- Image Gallery -->
      <div class="gallery-grid">
        <div class="gallery-item gallery-main">
          <?php if (!empty($propertyImages[0]['image_path'])): ?>
            <img src="tripsorus-admin/<?php echo htmlspecialchars($propertyImages[0]['image_path']); ?>"
              class="img-fluid hotel-img" alt="<?php echo htmlspecialchars($propertyName); ?>">
          <?php else: ?>
            <img src="images/river3.jpg" alt="Hotel exterior" class="img-fluid hotel-img">
          <?php endif; ?>
          <!-- View All Photos Link -->
          <a href="#" class="view-all-photos" data-bs-toggle="modal" data-bs-target="#photosModal">
            View all photos <i class="fas fa-chevron-right"></i>
          </a>
          <!-- Modal -->
          <div class="modal fade" id="photosModal" tabindex="-1" aria-labelledby="photosModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="photosModalLabel"><?php echo htmlspecialchars($propertyName); ?> - Photos
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="row g-3">
                    <?php foreach ($propertyImages as $img): ?>
                      <div class="col-md-4 col-sm-6">
                        <img src="tripsorus-admin/<?php echo htmlspecialchars($img['image_path']); ?>"
                          class="img-fluid rounded shadow-sm" alt="Property Photo">
                      </div>
                    <?php endforeach; ?>

                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php for ($i = 1; $i < 5; $i++): ?>
          <?php if (isset($propertyImages[$i])): ?>
            <div class="gallery-item rest-img">
              <img src="tripsorus-admin/<?php echo htmlspecialchars($propertyImages[$i]['image_path']); ?>"
                alt="<?php echo htmlspecialchars($propertyName . ' - Image ' . ($i + 1)); ?>">
            </div>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
    </section>
    <section class="property-info-section">
      <div class="container-fluid">
        <div class="row info-row abcd">
          <div class="col-12">
            <h2 class="section-title">About the Property</h2>
            <p>
              <?php
              $desc = "This beautiful property offers comfortable accommodations with modern amenities. " .
                "Guests can enjoy a relaxing stay with excellent service and convenient facilities.";
              if (!empty($propRow['description'])) {
                $desc = $propRow['description'];
              }
              $shortDesc = strlen($desc) > 120 ? substr($desc, 0, 120) . "..." : $desc;
              echo nl2br(htmlspecialchars($shortDesc));
              ?>
              <?php if (strlen($desc) > 120): ?>
                <button class="btn read-more-btn" data-bs-toggle="modal" data-bs-target="#descriptionModal">
                  Read More <span class="material-icons" style="font-size:16px;vertical-align:middle">chevron_right</span>
                </button>
              <?php endif; ?>
            </p>
          </div>

          <!-- Amenities -->
          <div class="col-12 mt-4">
            <h2 class="section-title">Amenities</h2>
            <div class="amenities-grid am">
              <?php
              $amenities = !empty($propertyAmenities)
                ? explode(',', $propertyAmenities)
                : ['Free WiFi', 'Air Conditioning', 'Swimming Pool', 'Restaurant', 'Parking'];
              $initialAmenities = array_slice($amenities, 0, 5);
              $remainingAmenities = array_slice($amenities, 5);
              $icons = [
                'wifi' => 'wifi',
                'free wifi' => 'wifi',
                'air conditioning' => 'ac_unit',
                'ac' => 'ac_unit',
                'swimming pool' => 'pool',
                'pool' => 'pool',
                'restaurant' => 'restaurant',
                'parking' => 'local_parking',
                'gym' => 'fitness_center',
                'fitness center' => 'fitness_center',
                '24/7 front desk' => 'support_agent',
                'front desk' => 'support_agent',
                'room service' => 'room_service',
                'breakfast' => 'free_breakfast',
                'spa' => 'spa',
                'bar' => 'sports_bar',
                'business center' => 'business_center',
                'laundry' => 'local_laundry_service'
              ];

              foreach ($initialAmenities as $amenity) {
                $amenity = trim($amenity);
                $aKey = strtolower($amenity);
                $icon = isset($icons[$aKey]) ? $icons[$aKey] : 'check_circle';
                echo '
            <div class="amenity-item">
              <span class="material-icons">' . $icon . '</span>
              <span class="amenity-text">' . htmlspecialchars($amenity) . '</span>
            </div>';
              }
              ?>
            </div>
            <div id="moreAmenities" style="display: none;" class="amenities-grid mt-3">
              <?php
              foreach ($remainingAmenities as $amenity) {
                $amenity = trim($amenity);
                $aKey = strtolower($amenity);
                $icon = isset($icons[$aKey]) ? $icons[$aKey] : 'check_circle';
                echo '
            <div class="amenity-item">
              <span class="material-icons">' . $icon . '</span>
              <span class="amenity-text">' . htmlspecialchars($amenity) . '</span>
            </div>';
              }
              ?>
            </div>
            <?php if (count($amenities) > 6): ?>
              <div class="text-center mt-3">
                <button id="viewAllBtn" class="btn view-all-btn">
                  View All <span class="material-icons" style="font-size:16px;vertical-align:middle">expand_more</span>
                </button>
                <button id="viewLessBtn" style="display: none;" class="btn btn-outline-primary view-all-btn">
                  View Less <span class="material-icons" style="font-size:16px;vertical-align:middle">expand_less</span>
                </button>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
    <!-- Description Modal -->
    <div class="modal fade" id="descriptionModal" tabindex="-1" aria-labelledby="descriptionModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="descriptionModalLabel">Property Description</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>
              <?php echo nl2br(htmlspecialchars($desc)); ?>
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const viewAllBtn = document.getElementById('viewAllBtn');
        const viewLessBtn = document.getElementById('viewLessBtn');
        const moreAmenities = document.getElementById('moreAmenities');

        if (viewAllBtn) {
          viewAllBtn.addEventListener('click', function () {
            moreAmenities.style.display = 'grid';
            viewAllBtn.style.display = 'none';
            if (viewLessBtn) viewLessBtn.style.display = 'inline-block';
          });
        }

        if (viewLessBtn) {
          viewLessBtn.addEventListener('click', function () {
            moreAmenities.style.display = 'none';
            viewLessBtn.style.display = 'none';
            if (viewAllBtn) viewAllBtn.style.display = 'inline-block';
          });
        }
      });
    </script>
    <!-- Booking Widget -->
    <section class="booking-widget">
      <form class="booking-form" method="GET" action="">
        <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : ''; ?>">
        <div class="form-group">
          <label for="checkin">Check-in date</label>
          <input type="date" id="checkin" name="checkin" value="<?php echo htmlspecialchars($checkin); ?>">
          <i class=""></i>
        </div>
        <div class="form-group">
          <label for="checkout">Check-out date</label>
          <input type="date" id="checkout" name="checkout" value="<?php echo htmlspecialchars($checkout); ?>">
          <i class=""></i>
        </div>
        <div class="form-group" style="position: relative;">
          <label for="guest-selector">Guests</label>
          <input type="text" id="guest-selector" readonly value="<?php echo $adults; ?> Adult<?php echo $adults != 1 ? 's' : ''; ?>, 
                       <?php echo $children; ?> Child<?php echo $children != 1 ? 'ren' : ''; ?>, 
                       <?php echo $rooms; ?> Room<?php echo $rooms != 1 ? 's' : ''; ?>">
          <i class="fas fa-user-friends form-icon"></i>
          <div id="guestModal" class="guest-modal" style="display:none;">
            <div class="counter-group">
              <span>Adults</span>
              <div>
                <button type="button" class="counter-btn" data-counter="adults" data-direction="-">-</button>
                <span id="adultsCounter">
                  <?php echo $adults; ?>
                </span>
                <button type="button" class="counter-btn" data-counter="adults" data-direction="+">+</button>
                <input type="hidden" id="adultsInput" name="adults" value="<?php echo $adults; ?>">
              </div>
            </div>
            <div class="counter-group">
              <span>Children</span>
              <div>
                <button type="button" class="counter-btn" data-counter="children" data-direction="-">-</button>
                <span id="childrenCounter">
                  <?php echo $children; ?>
                </span>
                <button type="button" class="counter-btn" data-counter="children" data-direction="+">+</button>
                <input type="hidden" id="childrenInput" name="children" value="<?php echo $children; ?>">
              </div>
            </div>
            <div class="counter-group">
              <span>Rooms</span>
              <div>
                <button type="button" class="counter-btn" data-counter="rooms" data-direction="-">-</button>
                <span id="roomsCounter">
                  <?php echo $rooms; ?>
                </span>
                <button type="button" class="counter-btn" data-counter="rooms" data-direction="+">+</button>
                <input type="hidden" id="roomsInput" name="rooms" value="<?php echo $rooms; ?>">
              </div>
            </div>
          </div>
        </div>
        <button type="submit" class="book-btn">Reserve Now</button>
      </form>
    </section>
    <section class="rooms-section">
      <h2 class="section-title">Available Rooms</h2>
      <?php
      foreach ($roomTypes as $roomType) {
        $roomTypeId = $roomType['id'];
        $roomTypeName = $roomType['name'];
        $roomTypeDescription = $roomType['description'];
        $roomAmenities = $roomType['amenities'];
        ?>
        <div class="room-card">
          <div class="room-header">
            <div id="availabil-room">
              <div class="mt-2 mb-2">
                <img src="tripsorus-admin/<?php echo htmlspecialchars($roomType['image']); ?>"
                  alt="<?php echo htmlspecialchars($roomTypeName); ?>" class="single-room-image">
                <a href="#" class="" data-bs-toggle="modal" data-bs-target="#photosModal<?php echo $roomTypeId; ?>">View
                  all photos </a>
              </div>
              <!-- View all photos link -->
              <h3 class="room-title fst-italic">
                <?php echo htmlspecialchars($roomTypeName); ?>
              </h3>
              <?php
              $words = explode(" ", strip_tags($roomTypeDescription));
              $wordCount = count($words);
              $shortDescription = $wordCount > 5 ? implode(" ", array_slice($words, 0, 5)) . "..." : $roomTypeDescription;
              ?>
              <div class="fst-italic">
                <?php echo nl2br(htmlspecialchars($shortDescription)); ?>
                <?php if ($wordCount > 5): ?>
                  <button type="button" class="btn p-0 ms-2 text-primary" data-bs-toggle="modal"
                    data-bs-target="#descModal<?php echo $roomTypeId; ?>">
                    Read More
                  </button>
                <?php endif; ?>
              </div>

              <?php if ($wordCount > 5): ?>
                <!-- Bootstrap Modal for Description -->
                <div class="modal fade" id="descModal<?php echo $roomTypeId; ?>" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Room Description</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <?php echo nl2br(htmlspecialchars($roomTypeDescription)); ?>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endif; ?>

              <?php if (!empty($roomAmenities)): ?>
                <div class="room-amenities">
                  <ul style="list-style-type: none; padding: 0;">
                    <?php
                    $amenities = explode(',', $roomAmenities);
                    foreach ($amenities as $amenity):
                      $amenity = trim($amenity);
                      if (!empty($amenity)):
                        $key = strtolower($amenity);
                        $iconClass = $amenityIcons[$key] ?? $amenityIcons['default'];
                        ?>
                        <li>
                          <i class="fas <?php echo $iconClass; ?>"></i>
                          <?php echo htmlspecialchars($amenity); ?>
                        </li>
                        <?php
                      endif;
                    endforeach;
                    ?>
                  </ul>
                </div>
              <?php endif; ?>
            </div>
            <!-- Modal for all photos -->
            <div class="modal fade" id="photosModal<?php echo $roomTypeId; ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">All Photos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <?php if (!empty($roomImages)): ?>
                        <?php foreach ($roomImages as $img): ?>
                          <div class="col-md-4 mb-3">
                            <img src="tripsorus-admin/<?php echo htmlspecialchars($img); ?>"
                              class="img-fluid rounded shadow-sm" alt="Room Image">
                          </div>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <p>No additional photos available.</p>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <div class="room-options-container">
            <?php
            foreach ($mealTypes as $mealName => $mealType) {
              $roomPriceData = getRoomPrice($pdo, $roomTypeId, $mealType, $checkin, $checkout, $promotions);
              $totalPrice = $roomPriceData['price']; // total price from DB
              $appliedPromotions = $roomPriceData['applied_promotions'];

              if ($totalPrice > 0) {
                $taxAmount = $totalPrice * 0.12; // 12% of total as tax
                $discountedPrice = $totalPrice - $taxAmount; // base/discounted price
          
                $isHighlighted = $mealName === 'With Breakfast' ? 'highlighted' : '';
                ?>
                <div class="room-option <?php echo $isHighlighted; ?>">
                  <div class="room-content">
                    <div class="option-header">
                      <h4><?php echo $mealName; ?></h4>
                      <div class="free-cancellation">
                        Free Cancellation before <?= $freeCancellation ?>
                      </div>
                      <ul class="option-benefits">
                        <?php if ($mealType == 'room_only'): ?>
                          <li><span class="material-icons small-icon">cancel</span> No meals included</li>
                        <?php elseif ($mealType == 'with_breakfast'): ?>
                          <li><span class="material-icons small-icon">free_breakfast</span> Breakfast included</li>
                        <?php elseif ($mealType == 'breakfast_lunch_dinner'): ?>
                          <li><span class="material-icons small-icon">restaurant</span> Breakfast, Lunch & Dinner included</li>
                        <?php endif; ?>
                        <?php if (!empty($appliedPromotions)): ?>
                          <?php foreach ($appliedPromotions as $promoName): ?>
                            <li><span class="material-icons small-icon">local_offer</span> Promotion Applied:
                              <?= htmlspecialchars($promoName) ?>
                            </li>
                          <?php endforeach; ?>
                        <?php endif; ?>
                        <li><span class="material-icons small-icon">currency_rupee</span> Book with ₹0 payment</li>
                        <li><span class="material-icons small-icon">account_balance_wallet</span> Pay at property</li>
                      </ul>
                    </div>

                    <div class="option-price-container">
                      <!-- Original price (crossed out) -->
                      <span class="original-price">₹<?php echo number_format($totalPrice + 200, 2); ?></span>

                      <!-- Discounted price (base price without tax) -->
                      <span class="discounted-price">₹<?php echo number_format($discountedPrice, 2); ?></span>

                      <!-- Taxes -->
                      <div class="taxes">+ ₹<?php echo number_format($taxAmount, 2); ?> Taxes & Fees per night</div>

                      <a href="cart.php?
              property_id=<?= urlencode($propertyId) ?>&
              property_name=<?= urlencode($propertyName) ?>&
              address=<?= urlencode($propertyAddress) ?>&
              city=<?= urlencode($propertyCity) ?>&
              country=<?= urlencode($propertyCountry) ?>&
              room_type_id=<?= urlencode($roomTypeId) ?>&
              room_name=<?= urlencode($roomTypeName) ?>&
              meal_name=<?= urlencode($mealName) ?>&
              price=<?= urlencode($discountedPrice) ?>&
              taxes=<?= urlencode($taxAmount) ?>&
              checkin=<?= urlencode($checkin) ?>&
              checkout=<?= urlencode($checkout) ?>&
              adults=<?= urlencode($adults) ?>&
              children=<?= urlencode($children) ?>&
              rooms=<?= urlencode($rooms) ?>" class="btn btn-primary">
                        SELECT ROOM
                      </a>
                      <div class="room-availability">Only 2 rooms left at this price!</div>
                    </div>
                  </div>
                </div>
                <?php
              }
            }
            ?>
          </div>

        </div>
        <?php
      }
      ?>
    </section>
    <section class="property-info-section">
      <div class="hotel-rules-container">
        <div class="hotel-name">Hotel Rules at <?php echo htmlspecialchars($propertyName); ?></div>
        <div class="check-in-out">
          Check-in: <?php echo $propertyCheckin; ?>
          | Check-out: <?php echo $propertyCheckout; ?>
        </div>
        <hr>
        <div class="rule-item">
          <p>Primary Guest should be at least 18 years of age.</p>
        </div>
        <div class="rules-categories">
          <a href="#" class="category-link btn btn-outline-secondary">Must Read Rules</a>
          <div class="divider"></div>
          <a href="#" class="category-link btn btn-outline-secondary">Guest Profile</a>
          <div class="divider"></div>
          <a href="#" class="category-link btn btn-outline-secondary">Guest Profile (Hourly)</a>
          <button class="view-all-btn" id="viewAllRulesBtn">Read All Property Rules</button>

        </div>
      </div>
    </section>
    <div class="popup-overlay" id="rulesPopup">
      <div class="popup-content">
        <button class="popup-close" id="closePopup">&times;</button>
        <h2 class="popup-title">House Rules & Information</h2>
        <div id="rulesContent">
        </div>
      </div>
    </div>
    <section class="reviews-section">
      <h2 class="section-title">Guest reviews</h2>
      <div class="review-score">
        <div class="score-circle">
          <span class="score-value">8.6</span>
          <span class="score-label">Excellent</span>
        </div>
        <div class="score-description">
          <p>Based on 428 verified guest reviews from actual stays at this property</p>
        </div>
      </div>
      <div class="review-card">
        <div class="review-header">
          <div class="reviewer-info">
            <div class="reviewer-avatar">JD</div>
            <div>
              <div class="reviewer-name">John D.</div>
              <div class="review-date">March 2024</div>
            </div>
          </div>
          <div class="review-rating">9.2</div>
        </div>
        <div class="review-content">
          <p>Excellent location right near the beach. The staff were very friendly and helpful. Room was clean
            and comfortable with a great view of the pool area. The breakfast buffet had a good variety of
            options. Would definitely stay here again on my next visit to Goa.</p>
        </div>
      </div>
      <div class="review-card">
        <div class="review-header">
          <div class="reviewer-info">
            <div class="reviewer-avatar">SM</div>
            <div>
              <div class="reviewer-name">Sarah M.</div>
              <div class="review-date">February 2024</div>
            </div>
          </div>
          <div class="review-rating">8.4</div>
        </div>
        <div class="review-content">
          <p>Great value for money. The pool area was lovely and well-maintained. The breakfast had good
            variety with both Western and Indian options. Only minor complaint was the WiFi was a bit slow
            at peak times. Housekeeping did an excellent job keeping our room clean throughout our stay.</p>
        </div>
      </div>
    </section>
    <section class="location-section">
      <h2 class="section-title">Location</h2>
      <p><i class="fas fa-map-marker-alt"></i>
        <?= htmlspecialchars($propertyAddress) ?>,
        <?= htmlspecialchars($propertyCity) ?>,
        <?= htmlspecialchars($propertyCountry) ?>
      </p>
      <div class="map-container">
        <?php if (!empty($propertyMapLink)): ?>
          <iframe id="property-map" src="<?php echo htmlspecialchars($propertyMapLink); ?>" allowfullscreen loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        <?php else: ?>
          <div
            style="height: 100%; display: flex; align-items: center; justify-content: center; background: #f8f9fa; color: #6c757d;">
            <div style="text-align: center;">
              <i class="fas fa-map-marked-alt" style="font-size: 48px; margin-bottom: 16px;"></i>
              <p style="margin: 0;">Map not available for this property</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <h3 class="detail-title">What's nearby</h3>
      <ul class="nearby-list">
        <li><i class="fas fa-umbrella-beach"></i> Candolim Beach - 2 min walk</li>
        <li><i class="fas fa-monument"></i> Fort Aguada - 10 min drive</li>
        <li><i class="fas fa-umbrella-beach"></i> Calangute Beach - 15 min walk</li>
        <li><i class="fas fa-plane"></i> Dabolim Airport - 45 min drive</li>
        <li><i class="fas fa-shopping-bag"></i> Candolim Market - 5 min walk</li>
        <li><i class="fas fa-utensils"></i> Multiple restaurants within 5 min walk</li>
      </ul>
    </section>
  </main>
  <!-- Footer -->
  <?php include 'footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const checkinInput = document.getElementById('checkin');
      const checkoutInput = document.getElementById('checkout');
      const today = new Date();
      const tomorrow = new Date(today);
      tomorrow.setDate(tomorrow.getDate() + 1);
      const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
      };
      const todayFormatted = formatDate(today);
      checkinInput.setAttribute('min', todayFormatted);
      checkoutInput.setAttribute('min', formatDate(tomorrow));
      if (!checkinInput.value) {
        checkinInput.value = todayFormatted;
      }
      if (!checkoutInput.value) {
        const tomorrowFormatted = formatDate(tomorrow);
        checkoutInput.value = tomorrowFormatted;
      }
      checkinInput.addEventListener('change', function () {
        const checkinDate = new Date(this.value);
        const newMinCheckout = new Date(checkinDate);
        newMinCheckout.setDate(newMinCheckout.getDate() + 1);
        checkoutInput.setAttribute('min', formatDate(newMinCheckout));
        const checkoutDate = new Date(checkoutInput.value);
        if (checkoutDate <= checkinDate) {
          checkoutInput.value = formatDate(newMinCheckout);
        }
      });
    });

    const discountedPriceEl = document.querySelector('.discounted-price');
    const taxesEl = document.querySelector('.taxes');
    if (discountedPriceEl && taxesEl) {
      let discountedPrice = parseFloat(discountedPriceEl.textContent.replace(/[₹,]/g, ''));
      let tax = (discountedPrice * 0.12).toFixed(2);
      taxesEl.textContent = '+ ₹' + tax + ' Taxes & Fees per night';
    }
    document.addEventListener("DOMContentLoaded", function () {
      const counters = {
        adults: {
          value: <?php echo (int) $adults; ?>,
          min: 1,
          element: document.getElementById("adultsCounter"),
          input: document.getElementById("adultsInput")
        },
        children: {
          value: <?php echo (int) $children; ?>,
          min: 0,
          element: document.getElementById("childrenCounter"),
          input: document.getElementById("childrenInput")
        },
        rooms: {
          value: <?php echo (int) $rooms; ?>,
          min: 1,
          element: document.getElementById("roomsCounter"),
          input: document.getElementById("roomsInput")
        }
      };

      const guestSelector = document.getElementById("guest-selector");
      const guestModal = document.getElementById("guestModal");

      function updateGuestText() {
        guestSelector.value = `${counters.adults.value} Adult${counters.adults.value !== 1 ? "s" : ""}, ` +
          `${counters.children.value} Child${counters.children.value !== 1 ? "ren" : ""}, ` +
          `${counters.rooms.value} Room${counters.rooms.value !== 1 ? "s" : ""}`;
      }
      updateGuestText();
      guestSelector.addEventListener("click", () => {
        guestModal.style.display = guestModal.style.display === "none" ? "block" : "none";
      });
      document.querySelectorAll(".counter-btn").forEach(btn => {
        btn.addEventListener("click", function () {
          const type = this.getAttribute("data-counter");
          const counter = counters[type];
          const increment = this.getAttribute("data-direction") === "+";
          if (increment) counter.value++;
          else if (counter.value > counter.min) counter.value--;
          counter.element.textContent = counter.value;
          counter.input.value = counter.value;
          updateGuestText();
        });
      });
      document.addEventListener("click", (e) => {
        if (!guestSelector.contains(e.target) && !guestModal.contains(e.target)) {
          guestModal.style.display = "none";
        }
      });
    });
    const ruleCategories = <?php echo json_encode($ruleCategories); ?>;

    function populateRulesPopup(categoryId = null) {
      const rulesContent = document.getElementById('rulesContent');
      rulesContent.innerHTML = '';

      const categories = categoryId ?
        ruleCategories.filter(c => c.id == categoryId) :
        ruleCategories;
      categories.forEach(category => {
        const categoryElement = document.createElement('div');
        categoryElement.className = 'rules-category';
        const titleElement = document.createElement('h3');
        titleElement.className = 'rules-category-title';
        titleElement.textContent = category.name.toUpperCase();
        categoryElement.appendChild(titleElement);
        category.rules.forEach(rule => {
          const listElement = document.createElement('ul');
          listElement.className = 'rules-list';
          rule.items.forEach(item => {
            const listItem = document.createElement('li');
            listItem.textContent = item;
            listElement.appendChild(listItem);
          });
          categoryElement.appendChild(listElement);
        });
        rulesContent.appendChild(categoryElement);
      });
    }

    function showRulesPopup(categoryId = null) {
      populateRulesPopup(categoryId);
      document.getElementById('rulesPopup').classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function hideRulesPopup() {
      document.getElementById('rulesPopup').classList.remove('active');
      document.body.style.overflow = '';
    }
    document.getElementById('viewAllRulesBtn').addEventListener('click', () => showRulesPopup());
    document.getElementById('closePopup').addEventListener('click', hideRulesPopup);
    document.getElementById('rulesPopup').addEventListener('click', function (e) {
      if (e.target === this) hideRulesPopup();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') hideRulesPopup();
    });
    document.querySelectorAll('.category-link').forEach(link => {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        const catId = this.getAttribute('data-id');
        showRulesPopup(catId);
      });
    });
  </script>
</body>

</html>