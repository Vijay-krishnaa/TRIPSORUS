<?php
session_start();
require_once 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['booking_id'])) {
  die("Booking ID is required.");
}
$bookingId = $_GET['booking_id'];
$stmt = $pdo->prepare("
    SELECT b.*, 
           u.first_name, u.last_name, u.email, u.phone,
           p.name AS property_name, p.city, p.country,
           pi.image_path AS property_image,
           r.name AS room_type_name
    FROM bookings b
    LEFT JOIN user u ON b.user_id = u.id
    LEFT JOIN properties p ON b.property_id = p.id
    LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_main = 1
    LEFT JOIN room_types r ON b.room_type_id = r.id
    WHERE b.booking_id = :booking_id
    LIMIT 1
");

$stmt->execute(['booking_id' => $bookingId]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$booking) {
  die("Booking not found.");
}
$stmtImg = $pdo->prepare("
    SELECT image_path 
    FROM property_images 
    WHERE property_id = :property_id AND is_main = 1
    LIMIT 1
");
$stmtImg->execute(['property_id' => $booking['property_id']]);
$image = $stmtImg->fetch(PDO::FETCH_ASSOC);
$hotelImage = $image ? "tripsorus-admin/" . $image['image_path'] : "assets/default-hotel.jpg";
$checkIn = new DateTime($booking['check_in']);
$checkOut = new DateTime($booking['check_out']);
$nights = $checkOut->diff($checkIn)->days;
$roomPrice = $nights > 0 ? $booking['amount'] / $nights : $booking['amount'];
$taxes = $booking['amount'] * 0.18;
$totalAmount = $booking['amount'] + $taxes;
$bookingRef = $booking['booking_code'] ?? $booking['booking_id'];
$guestName = trim($booking['first_name'] . " " . $booking['last_name']);
$guests = 2;
$roomTypeName = $booking['room_type_name'] ?? "Deluxe Room";
$city = $booking['city'] ?? "Not specified";
$country = $booking['country'] ?? "India";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Confirmation - TRIPSORUS</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="icon" href="images/favicon.ico" type="image/ico" />
  <link rel="stylesheet" href="styles/style.css">
  <style>
    .confirmation-hero {
      background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('<?php echo $hotelImage; ?>') center/cover no-repeat;
    }

    .confirmation-card {
      background-color: #fff;
      border-radius: 10px;
    }

    .detail-label {
      font-size: 0.9rem;
      color: #6c757d;
      margin-bottom: 0.25rem;
    }

    .summary-item {
      display: flex;
      justify-content: space-between;
      padding: 0.5rem 0;
      border-bottom: 1px solid #eee;
    }

    .summary-total {
      font-weight: bold;
      border-top: 2px solid #ddd;
      margin-top: 0.5rem;
      padding-top: 1rem;
      font-size: 1.1rem;
    }

    .amenity-badge {
      display: inline-block;
      background-color: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 4px;
      padding: 0.25rem 0.5rem;
      margin: 0.25rem;
      font-size: 0.85rem;
    }

    @media print {
      .no-print {
        display: none !important;
      }

      body {
        background-color: #fff !important;
      }

      .confirmation-hero {
        background: none !important;
        color: #000 !important;
        padding: 1rem 0 !important;
      }
    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>
  <section class="confirmation-hero text-center py-5 bg-dark text-white">
    <div class="container">
      <div class="confirmation-icon mb-3">
        <i class="fas fa-check-circle fa-4x text-success"></i>
      </div>
      <h1 class="mb-3">Booking Confirmed!</h1>
      <p class="lead fw-bold">Thank you for choosing TRIPSORUS. Your booking is now confirmed.</p>
      <div class="booking-ref mb-3">Booking Reference: <strong><?php echo htmlspecialchars($bookingRef); ?></strong>
      </div>
      <p>We've sent a confirmation email to
        <strong><?php echo htmlspecialchars($booking['email'] ?? 'Not provided'); ?></strong>
      </p>
      <div class="mt-4">
        <button class="btn btn-light me-2 no-print" onclick="window.print()">
          <i class="fas fa-print me-2"></i>Print Confirmation
        </button>
        <a href="index.php" class="btn btn-outline-light no-print">
          <i class="fas fa-home me-2"></i>Back to Home
        </a>
      </div>
    </div>
  </section>

  <div class="container my-5">
    <div class="row">
      <div class="col-lg-8">
        <div class="confirmation-card mb-4 p-4 border rounded shadow-sm">
          <h3 class="mb-4"><i class="fas fa-hotel me-2"></i>Booking Details</h3>
          <div class="row">
            <div class="col-md-4 mb-3">
              <img src="<?php echo htmlspecialchars($hotelImage); ?>" class="img-fluid rounded hotel-img"
                alt="<?php echo htmlspecialchars($booking['property_name']); ?>">
            </div>
            <div class="col-md-8">
              <h4><?php echo htmlspecialchars($booking['property_name']); ?></h4>
              <p class="text-muted">
                <i class="fas fa-map-marker-alt me-1"></i>
                <?php echo htmlspecialchars($city); ?>,
                <?php echo htmlspecialchars($country); ?>
              </p>
              <div class="row mt-3">
                <div class="col-6 col-md-4">
                  <p class="detail-label">Check-in</p>
                  <p><strong><?php echo htmlspecialchars($booking['check_in']); ?></strong></p>
                </div>
                <div class="col-6 col-md-4">
                  <p class="detail-label">Check-out</p>
                  <p><strong><?php echo htmlspecialchars($booking['check_out']); ?></strong></p>
                </div>
                <div class="col-6 col-md-4">
                  <p class="detail-label">Duration</p>
                  <p><strong><?php echo $nights; ?> Night<?php echo $nights > 1 ? 's' : ''; ?></strong></p>
                </div>
                <div class="col-6 col-md-4">
                  <p class="detail-label">Guests</p>
                  <p><strong><?php echo $guests; ?> Adults</strong></p>
                </div>
                <div class="col-6 col-md-4">
                  <p class="detail-label">Room Type</p>
                  <p><strong><?php echo htmlspecialchars($roomTypeName); ?></strong></p>
                </div>
                <div class="col-6 col-md-4">
                  <p class="detail-label">Booking Status</p>
                  <p><span class="badge bg-success"><?php echo htmlspecialchars($booking['status']); ?></span></p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Guest Info -->
        <div class="confirmation-card mb-4 p-4 border rounded shadow-sm">
          <h3 class="mb-4"><i class="fas fa-user me-2"></i>Guest Information</h3>
          <div class="row">
            <div class="col-md-6 mb-3">
              <p class="detail-label">First Name</p>
              <p><strong><?php echo htmlspecialchars($booking['first_name']); ?></strong></p>
            </div>
            <div class="col-md-6 mb-3">
              <p class="detail-label">Last Name</p>
              <p><strong><?php echo htmlspecialchars($booking['last_name']); ?></strong></p>
            </div>
            <div class="col-md-6 mb-3">
              <p class="detail-label">Contact Number</p>
              <p>
                <strong><?php echo htmlspecialchars($booking['phone'] ?? $booking['guest_phone'] ?? 'Not provided'); ?></strong>
              </p>
            </div>
            <div class="col-md-12">
              <p class="detail-label">Email Address</p>
              <p>
                <strong><?php echo htmlspecialchars($booking['email'] ?? $booking['guest_email'] ?? 'Not provided'); ?></strong>
              </p>
            </div>

          </div>
        </div>

        <!-- Next Steps -->
        <div class="confirmation-card mb-4 p-4 border rounded shadow-sm">
          <h3 class="mb-4"><i class="fas fa-list-alt me-2"></i>What's Next?</h3>
          <ul class="list-unstyled">
            <li class="mb-3"><i class="fas fa-envelope me-2"></i> Confirmation Email sent to
              <?php echo htmlspecialchars($booking['email'] ?? 'you'); ?>
            </li>
            <li class="mb-3"><i class="fas fa-bed me-2"></i> Present booking reference at check-in</li>
            <li><i class="fas fa-umbrella-beach me-2"></i> Enjoy your stay at
              <?php echo htmlspecialchars($booking['property_name']); ?>
            </li>
          </ul>
        </div>
      </div>

      <!-- RIGHT SIDE -->
      <div class="col-lg-4">
        <div class="confirmation-card mb-4 p-4 border rounded shadow-sm">
          <h3 class="mb-4"><i class="fas fa-receipt me-2"></i>Payment Summary</h3>
          <div class="summary-item">
            <span>Room Price (<?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?>)</span>
            <span>₹<?php echo number_format($booking['amount']); ?></span>
          </div>
          <div class="summary-item">
            <span>Taxes & Fees</span>
            <span>₹<?php echo number_format($taxes); ?></span>
          </div>
          <div class="summary-item summary-total">
            <span>Total Paid</span>
            <span>₹<?php echo number_format($totalAmount); ?></span>
          </div>
          <p class="mt-3"><span class="badge bg-success">Payment Completed</span></p>
        </div>

        <div class="confirmation-card mb-4 p-4 border rounded shadow-sm">
          <h3 class="mb-4"><i class="fas fa-question-circle me-2"></i>Need Help?</h3>
          <p><i class="fas fa-phone-alt me-2"></i> +91 1234567890</p>
          <p><i class="fas fa-envelope me-2"></i> support@tripsorus.com</p>
          <p><i class="fas fa-clock me-2"></i> 24/7 Customer Support</p>
        </div>
      </div>
    </div>
  </div>

  <?php include 'footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>