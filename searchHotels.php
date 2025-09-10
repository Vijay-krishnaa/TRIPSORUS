<?php session_start(); ?>
<?php
include 'db.php';
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
$sql = "SELECT
p.id,
p.name,
p.city AS location,
p.description,
p.amenities,
(SELECT pi.image_path FROM property_images pi WHERE pi.property_id = p.id LIMIT 1) AS image,
(SELECT MIN(rt.price) FROM room_types rt WHERE rt.property_id = p.id) AS price
FROM properties p
WHERE p.city LIKE :location
  AND p.status = 'approved'
LIMIT :limit OFFSET :offset";


$stmt = $pdo->prepare($sql);
$stmt->bindValue(':location', $searchTerm, PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$results = $stmt->fetchAll();
$countSql = "SELECT COUNT(*) as total 
             FROM properties 
             WHERE city LIKE :location 
               AND status = 'approved'";

$countStmt = $pdo->prepare($countSql);
$countStmt->bindValue(':location', $searchTerm, PDO::PARAM_STR);
$countStmt->execute();
$totalRow = $countStmt->fetch();
$totalHotels = $totalRow['total'];
$totalPages = ceil($totalHotels / $limit);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotels in
    <?php echo htmlspecialchars($location); ?> - TRIPSORUS
  </title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="icon" href="images/favicon.ico" type="image/png">
  <link rel="stylesheet" href="styles/style.css">
  <style>
    .hotel-card {
      border: 1px solid #e3e8ef;
      border-radius: 12px;
      overflow: hidden;
      transition: all 0.3s ease;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .hotel-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
    }

    .image-box {
      padding: 12px;
    }

    .image-box img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 10px;
    }

    .hotel-card .card-body {
      padding: 1rem;
    }

    .hotel-card .card-title {
      font-size: 1.15rem;
      font-weight: 600;
      color: #0a55ff;
    }

    .hotel-card .card-text {
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
      color: #555;
    }

    .hotel-card .badge {
      font-size: 0.75rem;
      padding: 0.1rem 0.1rem;
      border-radius: 6px;
    }

    .hotel-card .badge.bg-primary {
      background-color: #0a55ff !important;
    }

    .hotel-card .text-danger {
      font-size: 0.75rem;
      margin-top: 0.5rem;
    }

    .property-sidebar {
      display: flex;
      float: right;
      flex-direction: column;
      text-align: right;
      padding: 1rem;

    }

    .search--btn {
      margin-top: 30px;
    }

    .rating-box .badge {
      font-size: 0.8rem;
      padding: 0.4rem 0.7rem;
      border-radius: 8px;
    }

    .rating-box .reviews {
      font-size: 0.75rem;
      color: #666;
      margin-top: 0.3rem;
    }

    .price-box .price {
      font-size: 1.4rem;
      font-weight: 700;
      color: #0a55ff;
    }

    .price-box .taxes {
      font-size: 0.8rem;
      color: #888;
    }

    .property-sidebar .btn {
      font-size: 0.95rem;
      font-weight: 600;
      padding: 0.65rem;
      border-radius: 8px;
      background-color: #0a55ff;
      border: none;
      transition: background 0.25s ease;
    }

    .property-sidebar .btn:hover {
      background-color: #004ad8;
    }

    @media (max-width: 768px) {
      .image-box img {
        height: 160px;
      }

      .property-sidebar {
        border-left: none;
        border-top: 1px solid #e3e8ef;
        margin-top: 0.5rem;

      }
    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>
  <section class="search-header py-3 bg-light">
    <div class="container">
      <form action="searchHotels.php" method="GET">
        <input type="hidden" name="adults" id="adultsInput" value="<?php echo $adults; ?>">
        <input type="hidden" name="children" id="childrenInput" value="<?php echo $children; ?>">
        <input type="hidden" name="rooms" id="roomsInput" value="<?php echo $rooms; ?>">
        <input type="hidden" name="checkin" id="checkinHidden" value="<?php echo htmlspecialchars($checkin); ?>">
        <input type="hidden" name="checkout" id="checkoutHidden" value="<?php echo htmlspecialchars($checkout); ?>">
        <input type="hidden" name="location" id="locationHidden" value="<?php echo htmlspecialchars($location); ?>">
        <div class="row align-items-center">
          <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
            <label for="location" class="form-label">Destination</label>
            <input type="text" id="locationDisplay" name="location_display" class="form-control"
              placeholder="City, Hotel, or Area" value="<?php echo htmlspecialchars($location); ?>" required>
          </div>
          <div class="col-md-6 col-lg-2 mb-3 mb-lg-0">
            <label for="checkinDisplay" class="form-label">Check-in</label>
            <input type="date" id="checkinDisplay" name="checkin_display" class="form-control"
              value="<?php echo htmlspecialchars($checkin); ?>" required>
          </div>
          <div class="col-md-6 col-lg-2 mb-3 mb-lg-0">
            <label for="checkoutDisplay" class="form-label">Check-out</label>
            <input type="date" id="checkoutDisplay" name="checkout_display" class="form-control"
              value="<?php echo htmlspecialchars($checkout); ?>" required>
          </div>
          <div class="col-md-6 col-lg-4 mb-3 mb-lg-0">
            <label for="guest-selector" class="form-label">Guests & Rooms</label>
            <input type="text" id="guest-selector" class="form-control" readonly data-bs-toggle="modal"
              data-bs-target="#guestModal">
          </div>
          <div class="col-md-6 col-lg-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100 s-btn search--btn">Search</button>
          </div>
        </div>
      </form>
    </div>
    <!-- Guest Selection Modal -->
    <div class="modal fade" id="guestModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Guests & Rooms</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="counter-item">
              <label style="color: #0a55ff">Adults</label>
              <div class="counter-control">
                <button type="button" class="counter-btn" data-counter="adults" data-direction="-">-</button>
                <span class="counter-value" style="color: blue" id="adultsCounter"><?php echo $adults; ?></span>
                <button type="button" class="counter-btn" data-counter="adults" data-direction="+">+</button>
              </div>
            </div>
            <div class="counter-item">
              <label style="color: #0a55ff">Children</label>
              <div class="counter-control">
                <button type="button" class="counter-btn" data-counter="children" data-direction="-">-</button>
                <span class="counter-value" style="color: blue" id="childrenCounter"><?php echo $children; ?></span>
                <button type="button" class="counter-btn" data-counter="children" data-direction="+">+</button>
              </div>
            </div>
            <div class="counter-item">
              <label style="color: #0a55ff">Rooms</label>
              <div class="counter-control">
                <button type="button" class="counter-btn" data-counter="rooms" data-direction="-">-</button>
                <span class="counter-value" style="color: blue" id="roomsCounter"><?php echo $rooms; ?></span>
                <button type="button" class="counter-btn" data-counter="rooms" data-direction="+">+</button>
              </div>
            </div>
            <button type="button" class="btn btn-primary w-100 mt-3" data-bs-dismiss="modal">
              Done
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Main Content -->
  <div class="container my-4">
    <div class="row">
      <!-- Filters Sidebar -->
      <div class="col-lg-3">
        <div class="filter-sidebar">
          <h4 class="mb-3">Filter by:</h4>

          <!-- Budget Filter -->
          <div class="filter-section">
            <h6 class="filter-title">Your budget (per night)</h6>
            <div class="range-slider mb-2">
              <input type="range" class="form-range" min="400" max="6000" step="100" id="budgetRange" value="6000">
            </div>
            <div class="d-flex justify-content-between">
              <span>₹ 400</span>
              <span id="budgetValue">₹ 6,000+</span>
            </div>
          </div>

          <!-- Star Rating -->
          <div class="filter-section">
            <h6 class="filter-title">Star Rating</h6>
            <div class="filter-option form-check">
              <input class="form-check-input rating-filter" type="checkbox" id="rating5" value="5">
              <label class="form-check-label" for="rating5">
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
              </label>
            </div>
            <div class="filter-option form-check">
              <input class="form-check-input rating-filter" type="checkbox" id="rating4" value="4">
              <label class="form-check-label" for="rating4">
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="far fa-star text-warning"></i>
              </label>
            </div>
            <div class="filter-option form-check">
              <input class="form-check-input rating-filter" type="checkbox" id="rating3" value="3">
              <label class="form-check-label" for="rating3">
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="far fa-star text-warning"></i>
                <i class="far fa-star text-warning"></i>
              </label>
            </div>
          </div>

          <!-- Bed Size -->
          <div class="filter-section">
            <h6 class="filter-title">Bed Size</h6>
            <div class="filter-option form-check">
              <input class="form-check-input bed-filter" type="checkbox" id="bedQueen" value="Queen">
              <label class="form-check-label" for="bedQueen">Queen Bed</label>
            </div>
            <div class="filter-option form-check">
              <input class="form-check-input bed-filter" type="checkbox" id="bedKing" value="King">
              <label class="form-check-label" for="bedKing">King Bed</label>
            </div>
            <div class="filter-option form-check">
              <input class="form-check-input bed-filter" type="checkbox" id="bedDouble" value="Double">
              <label class="form-check-label" for="bedDouble">Double Bed</label>
            </div>
            <div class="filter-option form-check">
              <input class="form-check-input bed-filter" type="checkbox" id="bedTwin" value="Twin">
              <label class="form-check-label" for="bedTwin">Twin Beds</label>
            </div>
          </div>

          <!-- Popular Filters -->
          <div class="filter-section">
            <h6 class="filter-title">Popular Filters</h6>
            <div class="filter-option form-check">
              <input class="form-check-input amenity-filter" type="checkbox" id="breakfast" value="Breakfast">
              <label class="form-check-label" for="breakfast">Breakfast included <span
                  class="text-muted">(359)</span></label>
            </div>
            <div class="filter-option form-check">
              <input class="form-check-input amenity-filter" type="checkbox" id="freeCancel" value="Free Cancellation">
              <label class="form-check-label" for="freeCancel">Free cancellation <span
                  class="text-muted">(356)</span></label>
            </div>
            <div class="filter-option form-check">
              <input class="form-check-input amenity-filter" type="checkbox" id="aircon" value="Air Conditioning">
              <label class="form-check-label" for="aircon">Air conditioning <span
                  class="text-muted">(157)</span></label>
            </div>
            <div class="filter-option form-check">
              <input class="form-check-input amenity-filter" type="checkbox" id="balcony" value="Balcony">
              <label class="form-check-label" for="balcony">Balcony <span class="text-muted">(21)</span></label>
            </div>
          </div>

          <button class="btn btn-primary w-100 mt-2" id="applyFilters">Apply Filters</button>
          <button class="btn btn-outline-secondary w-100 mt-2" id="resetFilters">Reset All</button>
        </div>
      </div>

      <!-- Results Column -->
      <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="mb-0">
            <?php echo htmlspecialchars($location); ?>:
            <span id="filteredCount"><?php echo $totalHotels; ?></span> properties found
          </h4>

          <div class="sort-dropdown">
            <select class="form-select" id="sortResults">
              <option value="default" selected>Sort by: Our top picks</option>
              <option value="price-low">Price (low to high)</option>
              <option value="price-high">Price (high to low)</option>
              <option value="rating">Star Rating</option>
            </select>
          </div>
        </div>
        <div class="booking-categories">
          <button class="category-btn">Solo</button>
          <button class="category-btn">Corporate</button>
          <button class="category-btn">Single Lady</button>
          <button class="category-btn">Couple</button>
          <button class="category-btn">Student</button>
          <button class="category-btn">Budget Filer</button>
        </div>

        <!-- Property Listings -->
        <div id="propertyListings">
          <?php if (count($results) > 0): ?>
            <?php foreach ($results as $property): ?>
              <?php
              $finalPrice = isset($property['price']) ? (float) $property['price'] : 0;
              $basePrice = $finalPrice / 1.12;
              $taxAmount = $finalPrice - $basePrice;
              ?>
              <div class="card hotel-card mb-4 property-item"
                data-price="<?php echo isset($property['price']) ? $property['price'] : 0; ?>" data-rating="4"
                data-amenities="<?php echo htmlspecialchars($property['amenities']); ?>" data-beds="Queen,Double">

                <div class="row g-0">
                  <!-- Image -->
                  <div class="col-md-4">
                    <div class="image-box">
                      <img src="tripsorus-admin/<?php echo htmlspecialchars($property['image']); ?>" class="img-fluid"
                        alt="<?php echo htmlspecialchars($property['name']); ?>">
                    </div>
                  </div>

                  <!-- Middle Content -->
                  <div class="col-md-5">
                    <div class="card-body">
                      <h5 class="card-title">
                        <?php echo htmlspecialchars($property['name']); ?>
                      </h5>
                      <p class="card-text text-muted small">
                        <?php echo htmlspecialchars($property['location']); ?>
                      </p>

                      <!-- Room type -->
                      <div class="mb-2">
                        <span class="badge bg-light text-dark me-1">Guest room</span>
                      </div>
                      <!-- Amenities -->
                      <div class="highlights text-muted mb-2">
                        <?php
                        $highlights = [];
                        // Always add these static highlights
                        $highlights[] = '<div class="d-flex align-items-center mb-1">
                      <span class="material-icons me-2" style="font-size:18px;">payments</span> 
                      Pay at Property
                    </div>';

                        $highlights[] = '<div class="d-flex align-items-center mb-1">
                      <span class="material-icons me-2" style="font-size:18px;">cancel</span> 
                      Free Cancellation 1 min before Check-in
                    </div>';

                        $stmt = $pdo->prepare("
        SELECT COUNT(*) as cnt 
        FROM room_inventory 
        WHERE property_id = ? AND meal_type = 'with_breakfast'
    ");
                        $stmt->execute([$property['id']]);
                        $result = $stmt->fetch();

                        if ($result && $result['cnt'] > 0) {
                          $highlights[] = '<div class="d-flex align-items-center mb-1">
                          <span class="material-icons me-2" style="font-size:18px;">free_breakfast</span> 
                          Breakfast Included
                        </div>';
                        }

                        echo implode("", $highlights);
                        ?>
                      </div>
                      <p class="text-danger small mb-0">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        Only 5 rooms left at this price on our site
                      </p>
                    </div>
                  </div>
                  <!-- Right Sidebar -->
                  <div class="col-md-3">
                    <div class="property-sidebar">
                      <div class="rating-box">
                        <span class="badge bg-success">Excellent 8.7</span>
                        <p class="reviews">1,858 reviews</p>
                      </div>
                      <div class="price-box">
                        <h4 class="price">
                          ₹<?php echo number_format($basePrice, 2); ?>
                        </h4>
                        <p class="taxes">
                          + ₹<?php echo number_format($taxAmount, 2); ?> Taxes & Fees per night
                        </p>
                        <a href="details.php?id=<?php echo $property['id']; ?>&checkin=<?php echo $checkin; ?>&checkout=<?php echo $checkout; ?>&adults=<?php echo $adults; ?>&children=<?php echo $children; ?>&rooms=<?php echo $rooms; ?>"
                          class="btn btn-primary w-100">
                          See availability
                        </a>
                      </div>
                    </div>

                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>No properties found for "<?php echo htmlspecialchars($location); ?>".</p>
            <?php endif; ?>
          </div>

          <!-- Pagination -->
          <nav aria-label="Search results pagination" class="mt-4">
            <ul class="pagination justify-content-center">
              <li class="page-item <?php if ($page <= 1)
                echo 'disabled'; ?>">
                <a class="page-link"
                  href="?location=<?php echo urlencode($location); ?>&checkin=<?php echo $checkin; ?>&checkout=<?php echo $checkout; ?>&rooms=<?php echo $rooms; ?>&adults=<?php echo $adults; ?>&children=<?php echo $children; ?>&page=<?php echo $page - 1; ?>">Previous</a>
              </li>
              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php if ($i == $page)
                  echo 'active'; ?>">
                  <a class="page-link"
                    href="?location=<?php echo urlencode($location); ?>&checkin=<?php echo $checkin; ?>&checkout=<?php echo $checkout; ?>&rooms=<?php echo $rooms; ?>&adults=<?php echo $adults; ?>&children=<?php echo $children; ?>&page=<?php echo $i; ?>">
                    <?php echo $i; ?>
                  </a>
                </li>
              <?php endfor; ?>
              <li class="page-item <?php if ($page >= $totalPages)
                echo 'disabled'; ?>">
                <a class="page-link"
                  href="?location=<?php echo urlencode($location); ?>&checkin=<?php echo $checkin; ?>&checkout=<?php echo $checkout; ?>&rooms=<?php echo $rooms; ?>&adults=<?php echo $adults; ?>&children=<?php echo $children; ?>&page=<?php echo $page + 1; ?>">Next</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        // Date handling functionality
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const formatDate = (date) => {
          const year = date.getFullYear();
          const month = String(date.getMonth() + 1).padStart(2, '0');
          const day = String(date.getDate()).padStart(2, '0');
          return `${year}-${month}-${day}`;
        };
        const checkinDisplay = document.getElementById('checkinDisplay');
        const checkoutDisplay = document.getElementById('checkoutDisplay');
        const checkinHidden = document.getElementById('checkinHidden');
        const checkoutHidden = document.getElementById('checkoutHidden');
        const todayFormatted = formatDate(today);
        checkinDisplay.setAttribute('min', todayFormatted);
        checkoutDisplay.setAttribute('min', formatDate(tomorrow));
        if (!checkinDisplay.value) {
          checkinDisplay.value = todayFormatted;
          checkinHidden.value = todayFormatted;
        }
        if (!checkoutDisplay.value) {
          const tomorrowFormatted = formatDate(tomorrow);
          checkoutDisplay.value = tomorrowFormatted;
          checkoutHidden.value = tomorrowFormatted;
        }

        checkinDisplay.addEventListener('change', function () {
          const checkinDate = new Date(this.value);
          const newMinCheckout = new Date(checkinDate);
          newMinCheckout.setDate(newMinCheckout.getDate() + 1);
          checkoutDisplay.setAttribute('min', formatDate(newMinCheckout));
          const checkoutDate = new Date(checkoutDisplay.value);
          if (checkoutDate <= checkinDate) {
            const newCheckout = formatDate(newMinCheckout);
            checkoutDisplay.value = newCheckout;
            checkoutHidden.value = newCheckout;
          }

          checkinHidden.value = this.value;
        });
        checkoutDisplay.addEventListener('change', function () {
          checkoutHidden.value = this.value;
        });
        const locationDisplay = document.getElementById('locationDisplay');
        const locationHidden = document.getElementById('locationHidden');
        locationDisplay.addEventListener('change', function () {
          locationHidden.value = this.value;
        });

        // Initialize counters
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

        function updateGuestText() {
          guestSelector.value = `${counters.adults.value} Adult${counters.adults.value !== 1 ? "s" : ""}, ` +
            `${counters.children.value} Child${counters.children.value !== 1 ? "ren" : ""}, ` +
            `${counters.rooms.value} Room${counters.rooms.value !== 1 ? "s" : ""}`;
        }

        updateGuestText();
        document.querySelectorAll(".counter-btn").forEach((btn) => {
          btn.addEventListener("click", function () {
            const counterType = this.getAttribute("data-counter");
            const isIncrement = this.getAttribute("data-direction") === "+";
            const counter = counters[counterType];

            if (isIncrement) {
              counter.value++;
            } else if (counter.value > counter.min) {
              counter.value--;
            }
            counter.element.textContent = counter.value;
            counter.input.value = counter.value;

            updateGuestText();
          });
        });
        document.getElementById("guestModal").addEventListener("hidden.bs.modal", function () {
          updateGuestText();
        });

        // Filter functionality
        const budgetRange = document.getElementById('budgetRange');
        const budgetValue = document.getElementById('budgetValue');
        const ratingFilters = document.querySelectorAll('.rating-filter');
        const bedFilters = document.querySelectorAll('.bed-filter');
        const amenityFilters = document.querySelectorAll('.amenity-filter');
        const applyFiltersBtn = document.getElementById('applyFilters');
        const resetFiltersBtn = document.getElementById('resetFilters');
        const sortSelect = document.getElementById('sortResults');
        const propertyItems = document.querySelectorAll('.property-item');
        const filteredCount = document.getElementById('filteredCount');

        // Initialize budget slider
        budgetRange.addEventListener('input', function () {
          if (this.value == 6000) {
            budgetValue.textContent = '₹ 6,000+';
          } else {
            budgetValue.textContent = `₹ ${Number(this.value).toLocaleString()}`;
          }
        });

        // Apply filters function
        function applyFilters() {
          const maxPrice = parseInt(budgetRange.value);
          const selectedRatings = Array.from(ratingFilters)
            .filter(filter => filter.checked)
            .map(filter => parseInt(filter.value));

          const selectedBeds = Array.from(bedFilters)
            .filter(filter => filter.checked)
            .map(filter => filter.value.toLowerCase());

          const selectedAmenities = Array.from(amenityFilters)
            .filter(filter => filter.checked)
            .map(filter => filter.value.toLowerCase());

          let visibleCount = 0;

          propertyItems.forEach(item => {
            const itemPrice = parseInt(item.dataset.price);
            const itemAmenities = item.dataset.amenities.toLowerCase();
            const itemBeds = item.dataset.beds.toLowerCase();

            // Price filter
            if (itemPrice > maxPrice) {
              item.style.display = 'none';
              return;
            }

            // Rating filter (if any selected)
            if (selectedRatings.length > 0) {
              const itemRating = parseInt(item.dataset.rating);
              if (!selectedRatings.includes(itemRating)) {
                item.style.display = 'none';
                return;
              }
            }

            // Bed size filter (if any selected)
            if (selectedBeds.length > 0) {
              let hasSelectedBed = false;
              for (const bed of selectedBeds) {
                if (itemBeds.includes(bed)) {
                  hasSelectedBed = true;
                  break;
                }
              }
              if (!hasSelectedBed) {
                item.style.display = 'none';
                return;
              }
            }

            // Amenities filter (if any selected)
            if (selectedAmenities.length > 0) {
              let hasAllAmenities = true;
              for (const amenity of selectedAmenities) {
                if (!itemAmenities.includes(amenity)) {
                  hasAllAmenities = false;
                  break;
                }
              }
              if (!hasAllAmenities) {
                item.style.display = 'none';
                return;
              }
            }

            // If all filters passed, show the item
            item.style.display = '';
            visibleCount++;
          });

          filteredCount.textContent = visibleCount;
        }

        // Sort function
        function sortProperties() {
          const sortValue = sortSelect.value;
          const container = document.getElementById('propertyListings');
          const items = Array.from(propertyItems).filter(item => item.style.display !== 'none');

          items.sort((a, b) => {
            const aPrice = parseInt(a.dataset.price);
            const bPrice = parseInt(b.dataset.price);
            switch (sortValue) {
              case 'price-low':
                return aPrice - bPrice;
              case 'price-high':
                return bPrice - aPrice;
              case 'rating':
                const aRating = parseInt(a.dataset.rating);
                const bRating = parseInt(b.dataset.rating);
                return bRating - aRating;
              default:
                return 0;
            }
          });
          items.forEach(item => item.remove());
          items.forEach(item => container.appendChild(item));
        }
        applyFiltersBtn.addEventListener('click', applyFilters);
        resetFiltersBtn.addEventListener('click', function () {
          budgetRange.value = 6000;
          budgetValue.textContent = '₹ 6,000+';
          ratingFilters.forEach(filter => filter.checked = false);
          bedFilters.forEach(filter => filter.checked = false);
          amenityFilters.forEach(filter => filter.checked = false);
          propertyItems.forEach(item => item.style.display = '');
          filteredCount.textContent = propertyItems.length;
          sortSelect.value = 'default';
        });

        sortSelect.addEventListener('change', sortProperties);
      });
    </script>
</body>

</html>