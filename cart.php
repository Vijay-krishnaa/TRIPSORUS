<?php
session_start();
require_once 'db.php';
$userDetails = [
  'first_name' => '',
  'last_name' => '',
  'email' => '',
  'phone' => '',
  'title' => ''
];

if (isset($_SESSION['user_id'])) {
  $userId = $_SESSION['user_id'];

  $stmt = $pdo->prepare("SELECT first_name, last_name, email, phone
                           FROM user 
                           WHERE id = :id LIMIT 1");
  $stmt->execute(['id' => $userId]);
  $fetchedDetails = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($fetchedDetails) {
    $userDetails = $fetchedDetails;
  }
}
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$stmt = $pdo->prepare("SELECT first_name, last_name, email, phone FROM user WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $userId]);
$userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userDetails) {
  $userDetails = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => ''
  ];
}
$roomTypeId = $_GET['room_type_id'] ?? null;
$roomName = $_GET['room_name'] ?? 'Unknown';
$mealName = $_GET['meal_name'] ?? 'Room Only';
$roomPrice = $_GET['price'] ?? 0;
$taxes = $_GET['taxes'] ?? 0;
$rooms = $_GET['rooms'] ?? 1;
$checkinDate = $_GET['checkin'] ?? null;
$checkoutDate = $_GET['checkout'] ?? null;

if ($checkinDate && $checkoutDate) {
  $nights = (strtotime($checkoutDate) - strtotime($checkinDate)) / 86400;
  $nights = ($nights > 0) ? $nights : 1;
} else {
  $nights = 1;
}
$subtotal = $roomPrice * $rooms * $nights;
$totalAmount = $subtotal + $taxes;

if ($nights <= 0)
  $nights = 1;
$subtotal = $roomPrice * $rooms * $nights;
$totalAmount = $subtotal + $taxes;
$availability = $_GET['availability'] ?? 'N/A';
$mealPlanCode = 'EP';
if ($mealName === 'With Breakfast') {
  $mealPlanCode = 'CP';
} elseif ($mealName === 'Breakfast+lunch/dinner') {
  $mealPlanCode = 'MAP';
}
$_SESSION['selected_room'] = [
  'room_type_id' => $roomTypeId,
  'room_name' => $roomName,
  'meal_name' => $mealName,
  'meal_plan_code' => $mealPlanCode,
  'room_price' => $roomPrice,
  'taxes' => $taxes,
  'availability' => $availability
];

$selectedRoom = $_SESSION['selected_room'] ?? null;
$roomPrice = $selectedRoom['room_price'] ?? 7700;
$taxes = $selectedRoom['taxes'] ?? 1356;
$totalAmount = $roomPrice + $taxes;
$bookingDetails = [
  'property_id' => $_GET['property_id'] ?? '',
  'property_image' => $_GET['property_image'] ?? 'images/goa.jpg',
  'property_name' => $_GET['property_name'] ?? 'Unknown Hotel',
  'city' => $_GET['city'] ?? '',
  'country' => $_GET['country'] ?? '',
  'room_name' => $_GET['room_name'] ?? 'Standard Room',
  'meal_name' => $mealName,
  'meal_plan_code' => $mealPlanCode,
  'max_guests' => $_GET['adults'] ?? '2',
  'amenities' => $_GET['amenities'] ?? 'WiFi, AC, TV',
  'checkin_date' => $_GET['checkin'] ?? 'N/A',
  'checkout_date' => $_GET['checkout'] ?? 'N/A',
  'admin_id' => $_GET['admin_id'] ?? 1
];

if (
  (!isset($_GET['property_image']) || $bookingDetails['property_image'] === 'images/goa.jpg')
  && !empty($bookingDetails['property_id'])
) {
  $stmt = $pdo->prepare("
        SELECT image_path 
        FROM property_images 
        WHERE property_id = :pid AND is_main = 1 
        LIMIT 1
    ");
  $stmt->execute([':pid' => $bookingDetails['property_id']]);
  $mainImage = $stmt->fetchColumn();

  if ($mainImage) {
    $bookingDetails['property_image'] = $mainImage;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Complete Booking - TRIPSORUS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
  <link rel="icon" href="images/favicon.ico" type="image/ico" />
  <link rel="stylesheet" href="styles/style.css">
  <style>
    .meal-plan-badge {
      background-color: #0a55ff;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: bold;
      margin-left: 8px;
    }

    .booking-card {
      background: white;
      position: relative;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .summary-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }

    .payment-method {
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      margin-bottom: 15px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .payment-method.active {
      border-color: #0a55ff;
      background-color: #f8f9ff;
    }

    .hotel-img {
      border-radius: 10px;
      height: 200px;
      object-fit: cover;
      width: 100%;
    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>
  <section class="booking-header bg-light py-3">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-8">
          <h3>Complete Your Booking</h3>
          <div class="d-flex align-items-center">
            <div class="me-3">
              <i class="fas fa-check-circle text-success me-1"></i>
              <span>1. Your Details</span>
            </div>
            <div class="me-3">
              <i class="fas fa-check-circle text-success me-1"></i>
              <span>2. Payment</span>
            </div>
            <div>
              <i class="fas fa-circle text-primary me-1"></i>
              <span class="fw-bold">3. Confirm</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <div class="container mb-5 mt-4">
    <form method="POST" action="completeBooking.php">
      <div class="row">
        <div class="col-lg-8">
          <div class="booking-card">
            <div class="row">
              <div class="col-md-4 mb-3 mb-md-0">
                <img src="tripsorus-admin/<?php echo htmlspecialchars($bookingDetails['property_image']); ?>"
                  class="img-fluid hotel-img" alt="<?php echo htmlspecialchars($bookingDetails['property_name']); ?>">
              </div>
              <div class="col-md-8">
                <h4>
                  <?php echo htmlspecialchars($bookingDetails['property_name']); ?>
                </h4>
                <p class="text-muted">
                  <i class="fas fa-map-marker-alt me-1"></i>
                  <?php echo htmlspecialchars($bookingDetails['city']); ?>,
                  <?php echo htmlspecialchars($bookingDetails['country']); ?>
                </p>
                <div class="mt-3">
                  <h5>Your Room</h5>
                  <p>
                    <?php echo htmlspecialchars($bookingDetails['room_name']); ?>
                    <span class="meal-plan-badge" title="<?php echo htmlspecialchars($bookingDetails['meal_name']); ?>">
                      <?php echo htmlspecialchars($bookingDetails['meal_plan_code']); ?>
                    </span>
                  </p>
                  <p><i class="fas fa-utensils me-2"></i> Meal Plan:
                    <?php echo htmlspecialchars($bookingDetails['meal_name']); ?>
                  </p>
                  <p><i class="fas fa-user me-2"></i> Max Guests:
                    <?php echo htmlspecialchars($bookingDetails['max_guests']); ?>
                  </p>
                  <p><i class="fas fa-rupee-sign me-2"></i> Price: ₹
                    <?php echo number_format($roomPrice); ?>
                  </p>

                  <p><i class="fas fa-calendar-check me-2"></i> Check-in:
                    <?php echo htmlspecialchars($bookingDetails['checkin_date']); ?>
                  </p>
                  <p><i class="fas fa-calendar-times me-2"></i> Check-out:
                    <?php echo htmlspecialchars($bookingDetails['checkout_date']); ?>
                  </p>
                  <p><b>Amenities:</b>
                    <?php echo htmlspecialchars($bookingDetails['amenities']); ?>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Guest Information -->
          <div class="booking-card">
            <h4 class="mb-3">Guest Details</h4>
            <div class="mb-3">
              <label class="form-label me-3">
                <input type="radio" name="guest_for" value="myself" checked> Myself
              </label>
              <label class="form-label">
                <input type="radio" name="guest_for" value="someone_else"> Someone Else
              </label>
            </div>
            <div class="row">
              <div class="col-md-2 mb-3">
                <label for="title" class="form-label">Title</label>
                <select class="form-control" id="title" name="title" required>
                  <option value="Mr" <?php echo ($userDetails['title'] ?? '') === 'Mr' ? 'selected'
                    : ''; ?>>Mr</option>
                  <option value="Ms" <?php echo ($userDetails['title'] ?? '') === 'Ms' ? 'selected'
                    : ''; ?>>Ms</option>
                  <option value="Mrs" <?php echo ($userDetails['title'] ?? '') === 'Mrs' ? 'selected'
                    : ''; ?>>Mrs</option>
                  <option value="Dr" <?php echo ($userDetails['title'] ?? '') === 'Dr' ? 'selected'
                    : ''; ?>>Dr</option>
                </select>
              </div>
              <div class="col-md-5 mb-3">
                <label for="firstName" class="form-label">First Name</label>
                <input type="text" class="form-control" id="firstName" name="first_name"
                  value="<?php echo htmlspecialchars($userDetails['first_name']); ?>" required>
              </div>
              <div class="col-md-5 mb-3">
                <label for="lastName" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lastName" name="last_name"
                  value="<?php echo htmlspecialchars($userDetails['last_name']); ?>" required>
              </div>
            </div>
            <!-- Email + Phone -->
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="guest_email"
                  value="<?php echo htmlspecialchars($userDetails['email']); ?>" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">Mobile Number</label>
                <input type="tel" class="form-control" id="phone" name="guest_phone"
                  value="<?php echo htmlspecialchars($userDetails['phone']); ?>" required>
              </div>
            </div>

            <!-- GST Details -->
            <div class="mb-3">
              <label><input type="checkbox" id="gstCheck" name="gst_enabled" value="1"> Enter GST Details
                (Optional)</label>
            </div>
            <div id="gstFields" style="display:none;">
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label for="gstNumber" class="form-label">Registration No.</label>
                  <input type="text" class="form-control" id="gstNumber" name="gst_number">
                </div>
                <div class="col-md-4 mb-3">
                  <label for="gstCompanyName" class="form-label">Company Name</label>
                  <input type="text" class="form-control" id="gstCompanyName" name="gst_company_name">
                </div>
                <div class="col-md-4 mb-3">
                  <label for="gstCompanyAddress" class="form-label">Company Address</label>
                  <input type="text" class="form-control" id="gstCompanyAddress" name="gst_company_address">
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label for="guestName" class="form-label">Other Guest(s)</label>
              <input type="text" class="form-control" id="guestName" name="guest_name"
                value="<?php echo htmlspecialchars($userDetails['first_name'] . ' ' . $userDetails['last_name']); ?>">
            </div>
            <div class="mb-3">
              <label for="specialRequests" class="form-label">Special Requests (Optional)</label>
              <textarea class="form-control" id="specialRequests" name="special_requests" rows="3"
                placeholder="Any special requests?"></textarea>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="booking-card">
            <h4 class="mb-3">Payment Method</h4>
            <!-- Pay at Property -->
            <div class="payment-method" data-method="pay_at_property" onclick="selectPayment('pay_at_property')">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_type" id="payAtProperty"
                  value="pay_at_property">
                <label class="form-check-label fw-bold" for="payAtProperty">Pay at Property</label>
              </div>
              <div class="mt-2" id="payAtPropertyFields" style="display: none;">
                <p>You will pay at the property during check-in.</p>
              </div>
            </div>
            <!-- Credit/Debit Card -->
            <div class="payment-method active" data-method="credit_card" onclick="selectPayment('credit_card')">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_type" id="creditCard" value="credit_card"
                  checked>
                <label class="form-check-label fw-bold" for="creditCard">Credit/Debit Card</label>
              </div>
              <div class="row mt-2" id="creditCardFields">
                <div class="col-md-6 mb-3">
                  <label for="cardNumber" class="form-label">Card Number</label>
                  <input type="text" class="form-control" id="cardNumber" name="card_number"
                    placeholder="1234 5678 9012 3456">
                </div>
                <div class="col-md-3 mb-3">
                  <label for="expiry" class="form-label">Expiry</label>
                  <input type="text" class="form-control" id="expiry" name="expiry" placeholder="MM/YY">
                </div>
                <div class="col-md-3 mb-3">
                  <label for="cvv" class="form-label">CVV</label>
                  <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123">
                </div>
              </div>
              <div class="mb-3">
                <label for="cardName" class="form-label">Name on Card</label>
                <input type="text" class="form-control" id="cardName" name="card_name" placeholder="RAHUL SHARMA">
              </div>
            </div>
            <!-- UPI -->
            <div class="payment-method" data-method="upi" onclick="selectPayment('upi')">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_type" id="upi" value="upi">
                <label class="form-check-label fw-bold" for="upi">UPI</label>
              </div>
              <div class="mt-2" id="upiFields" style="display: none;">
                <div class="mb-3">
                  <label for="upiId" class="form-label">UPI ID</label>
                  <input type="text" class="form-control" id="upiId" name="upi_id" placeholder="rahul.sharma@upi">
                </div>
              </div>
            </div>
            <!-- Net Banking -->
            <div class="payment-method" data-method="net_banking" onclick="selectPayment('net_banking')">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_type" id="netBanking" value="net_banking">
                <label class="form-check-label fw-bold" for="netBanking">Net Banking</label>
              </div>
              <div class="mt-2" id="netbankingFields" style="display: none;">
                <select class="form-select" name="bank_name">
                  <option selected>Select your bank</option>
                  <option>State Bank of India</option>
                  <option>HDFC Bank</option>
                  <option>ICICI Bank</option>
                  <option>Axis Bank</option>
                  <option>Kotak Mahindra Bank</option>
                </select>
              </div>
            </div>
          </div>

        </div>

        <div class="col-lg-4">
          <div class="booking-card">
            <h4 class="mb-3">Order Summary</h4>
            <div class="summary-item">
              <span>Room (
                <?php echo $rooms; ?> ×
                <?php echo $nights; ?> night
                <?php echo $nights > 1 ? 's' : ''; ?>)
              </span>
              <span>₹
                <?php echo number_format($roomPrice * $rooms * $nights); ?>
              </span>
            </div>
            <div class="summary-item">
              <span>Taxes & Fees</span>
              <span>₹
                <?php echo number_format($taxes); ?>
              </span>
            </div>
            <hr>
            <div class="summary-item mt-3">
              <span>Coupon Discount</span>
              <span class="text-success" id="discountAmount">- ₹ 0</span>
            </div>
            <div class="mt-2">
              <div class="input mb-3 d-flex">
                <input type="text" class="form-control wd" id="couponCode" placeholder="Coupon code">
                <button class="btn btn-primary" type="button" onclick="applyManualCoupon()">Apply</button>
              </div>
              <div id="couponMessage" class="d-flex flex-wrap gap-2 mt-2"></div>
            </div>
            <hr>
            <div class="summary-item summary-total d-flex justify-content-between fw-bold">
              <span>Total</span>
              <span id="totalAmount">₹
                <?php echo number_format($totalAmount); ?>
              </span>
            </div>
            <div class="alert alert-info mt-3">
              <i class="fas fa-info-circle me-2"></i> Free cancellation 1m before check-in
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="termsCheck" required>
              <label class="form-check-label" for="termsCheck">
                I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a>
              </label>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">
              <i class="fas fa-lock me-2"></i> Complete Booking
            </button>

            <p class="text-muted small mt-2">
              <i class="fas fa-shield-alt me-1"></i> Your payment is secured with SSL encryption
            </p>
          </div>
          <div class="booking-card mt-3">
            <h5 class="mb-3">Need help with your booking?</h5>
            <p><i class="fas fa-phone-alt me-2"></i> Call us: +91 1234567890</p>
            <p><i class="fas fa-envelope me-2"></i> Email: support@tripsorus.com</p>
          </div>
        </div>
      </div>
      <!-- Hidden booking details -->
      <input type="hidden" name="user_id"
        value="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>">
      <input type="hidden" name="admin_id" value="<?php echo $bookingDetails['admin_id']; ?>">
      <input type="hidden" name="property_id" value="<?php echo $_GET['property_id'] ?? 1; ?>">
      <input type="hidden" name="property_name"
        value="<?php echo htmlspecialchars($bookingDetails['property_name']); ?>">
      <input type="hidden" name="room_type_id" value="<?php echo $selectedRoom['room_type_id'] ?? 1; ?>">
      <input type="hidden" name="room_name" value="<?php echo htmlspecialchars($bookingDetails['room_name']); ?>">
      <input type="hidden" name="meal_type" value="<?php echo htmlspecialchars($bookingDetails['meal_name']); ?>">
      <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($bookingDetails['checkin_date']); ?>">
      <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($bookingDetails['checkout_date']); ?>">
      <input type="hidden" name="amount" id="finalAmount" value="<?php echo $totalAmount; ?>">
      <input type="hidden" name="applied_coupon" id="appliedCouponField" value="">
      <input type="hidden" name="amount" id="finalAmount" value="<?php echo $totalAmount; ?>">
      <input type="hidden" name="rooms" value="<?php echo $rooms; ?>">
      <input type="hidden" name="nights" value="<?php echo $nights; ?>">
      <input type="hidden" name="paymentMethod" id="paymentMethodField" value="creditCard">
      <input type="hidden" name="special_requests" id="specialRequestsField" value="">
      <input type="hidden" name="gst_enabled" id="gstEnabledField" value="0">
      <input type="hidden" name="gst_number" id="gstNumberField" value="">
      <input type="hidden" name="gst_company_name" id="gstCompanyNameField" value="">
      <input type="hidden" name="gst_company_address" id="gstCompanyAddressField" value="">
    </form>
  </div>
  <!-- Footer -->
  <?php include 'footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let availableVouchers = [];
    let appliedCoupon = null;
    let originalTotal = <?php echo $totalAmount; ?>;
    async function loadVouchersForBooking() {
      try {
        const res = await fetch('tripsorus-admin/api/vouchers_sub.php/vouchers');
        const vouchers = await res.json();
        availableVouchers = vouchers;

        displayVouchers();
        applyVoucher();
      } catch (err) {
        console.error('Failed to load vouchers:', err);
      }
    }

    function displayVouchers() {
      const couponDiv = document.getElementById('couponMessage');
      couponDiv.innerHTML = '';

      availableVouchers.forEach(v => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-outline-primary fw-bold shadow-sm';
        btn.style.padding = '8px 16px';
        btn.style.borderRadius = '8px';
        btn.style.fontSize = '0.9rem';
        btn.style.transition = '0.2s';
        btn.style.color = 'black'
        btn.style.background = '#8ab2dcff'
        btn.onmouseover = () => btn.style.backgroundColor = '#64a9f3ff';
        btn.onmouseout = () => btn.style.backgroundColor = '#0d6efd';
        btn.textContent = v.code + (v.discount_type === 'percentage' ? ` (${v.discount_value}%)` :
          ` (₹${v.discount_value})`);
        btn.onclick = () => applyVoucher(v.code);
        couponDiv.appendChild(btn);
      });
    }

    function applyVoucher(code = null) {
      let coupon = null;

      if (code) {
        coupon = availableVouchers.find(v => v.code === code);
        document.getElementById('couponCode').value = code;
      } else {
        coupon = availableVouchers
          .filter(v => new Date(v.expiry_date) > new Date())
          .sort((a, b) => {
            const aAmount = a.discount_type === 'percentage' ? originalTotal * (a.discount_value / 100) : a
              .discount_value;
            const bAmount = b.discount_type === 'percentage' ? originalTotal * (b.discount_value / 100) : b
              .discount_value;
            return bAmount - aAmount;
          })[0];

        if (coupon) {
          document.getElementById('couponCode').value = coupon.code;
        }
      }
      appliedCoupon = coupon;
      let discount = 0;
      if (coupon) {
        discount = coupon.discount_type === 'percentage' ? Math.round(originalTotal * (coupon.discount_value / 100)) :
          coupon.discount_value;
      }
      document.getElementById('discountAmount').textContent = `- ₹ ${discount.toLocaleString()}`;
      document.getElementById('totalAmount').textContent = `₹ ${(originalTotal - discount).toLocaleString()}`;
      document.getElementById('finalAmount').value = originalTotal - discount;
      document.getElementById('appliedCouponField').value = coupon ? coupon.code : '';
    }

    function applyManualCoupon() {
      const codeInput = document.getElementById('couponCode').value.trim().toUpperCase();
      if (!codeInput) return alert('Please enter a coupon code');

      const found = availableVouchers.find(v => v.code.toUpperCase() === codeInput);
      if (found) {
        applyVoucher(found.code);
        alert(`Coupon applied: ${found.code}`);
      } else {
        alert('Invalid coupon code');
      }
    }

    function selectPayment(method) {
      document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
      document.getElementById('creditCardFields').style.display = 'none';
      document.getElementById('upiFields').style.display = 'none';
      document.getElementById('netbankingFields').style.display = 'none';
      document.getElementById('payAtPropertyFields').style.display = 'none';
      const selectedMethod = document.querySelector(`.payment-method[data-method="${method}"]`);
      if (selectedMethod) {
        selectedMethod.classList.add('active');
        selectedMethod.querySelector('input[type="radio"]').checked = true;

        if (method === 'credit') {
          document.getElementById('creditCardFields').style.display = 'block';
        } else if (method === 'upi') {
          document.getElementById('upiFields').style.display = 'block';
        } else if (method === 'netbanking') {
          document.getElementById('netbankingFields').style.display = 'block';
        } else if (method === 'payAtProperty') {
          document.getElementById('payAtPropertyFields').style.display = 'block';
        }
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      loadVouchersForBooking();
      document.getElementById('creditCardFields').style.display = 'block';
    });
    document.getElementById("gstCheck").addEventListener("change", function () {
      document.getElementById("gstFields").style.display = this.checked ? "block" : "none";
    });
  </script>
</body>

</html>