<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TRIPSORUS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
  <link rel="icon" href="images/favicon.ico" type="image/png" />
  <link rel="stylesheet" href="styles/style.css" />
  <style>
    .search-section {
      background: linear-gradient(135deg, #0a55ff 0%, #182848 100%);
      border-radius: 10px;
      padding: 25px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
      color: white;
    }

    .search-section h5 {
      color: white;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .search-section .form-control {
      background-color: rgba(255, 255, 255, 0.9);
      border: none;
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 15px;
    }

    .search-section .form-control:focus {
      box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
    }

    .search-btn-main {
      background-color: #ff6b6b;
      color: white;
      border: none;
      padding: 12px;
      margin-bottom: 15px;
      border-radius: 8px;
      width: 110%;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .search-btn-main:hover {
      background-color: #ff5252;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
    }

    .search-section label {
      color: rgba(255, 255, 255, 0.9);
      font-weight: 500;
      margin-bottom: 5px;
      display: block;
    }

    .hotel-card-compact {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .hotel-card-compact:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .hotel-img-compact {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }

    .section-title-boxed {
      position: relative;
      padding-bottom: 10px;
      margin-bottom: 25px;
      font-weight: 600;
    }

    .section-title-boxed:after {
      content: "";
      position: absolute;
      left: 0;
      bottom: 0;
      width: 50px;
      height: 3px;
      background: linear-gradient(to right, #4b6cb7, #182848);
    }

    @media (max-width: 767.98px) {
      .search-section {
        margin-bottom: 30px;
      }

      .hotel-img-compact {
        height: 150px;
      }
    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>
  <div class="container my-4">
    <div class="content-box">
      <div class="row mb-5">
        <div class="col-12">
          <div class="search-section">
            <h5 class="mb-3">Find your perfect stay</h5>
            <form action="searchHotels.php" method="GET">
              <div class="row">
                <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                  <label for="location">Destination</label>
                  <input type="text" name="location" class="form-control" placeholder="City, Hotel, or Area" required />
                </div>
                <div class="col-md-6 col-lg-2 mb-3 mb-lg-0">
                  <label for="checkin">Check-in</label>
                  <input type="date" name="checkin" id="checkin" class="form-control" required />
                </div>
                <div class="col-md-6 col-lg-2 mb-3 mb-lg-0">
                  <label for="checkout">Check-out</label>
                  <input type="date" name="checkout" id="checkout" class="form-control" required />
                </div>

                <!-- Guest Selector -->
                <div class="col-md-6 col-lg-4 mb-3 mb-lg-0">
                  <label for="guest-selector">Guests & Rooms</label>
                  <input type="text" id="guest-selector" class="form-control" readonly
                    value="2 Adults, 0 Children, 1 Room" data-bs-toggle="modal" data-bs-target="#guestModal" />
                  <input type="hidden" name="adults" id="adults" value="2">
                  <input type="hidden" name="children" id="children" value="0">
                  <input type="hidden" name="rooms" id="rooms" value="1">
                </div>
                <!-- Guest Selection Modal -->
                <div class="modal fade" id="guestModal" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-body p-4">
                        <div class="counter-item">
                          <label style="color: #0a55ff">Adults</label>
                          <div class="counter-control">
                            <button type="button" class="counter-btn">-</button>
                            <span class="counter-value" style="color: blue">2</span>
                            <button type="button" class="counter-btn">+</button>
                          </div>
                        </div>

                        <div class="counter-item">
                          <label style="color: #0a55ff">Children</label>
                          <div class="counter-control">
                            <button type="button" class="counter-btn">-</button>
                            <span class="counter-value" style="color: blue">0</span>
                            <button type="button" class="counter-btn">+</button>
                          </div>
                        </div>

                        <div class="counter-item">
                          <label style="color: #0a55ff">Rooms</label>
                          <div class="counter-control">
                            <button type="button" class="counter-btn">-</button>
                            <span class="counter-value" style="color: blue">1</span>
                            <button type="button" class="counter-btn">+</button>
                          </div>
                        </div>

                        <button type="button" class="btn btn-primary w-100 mt-3" data-bs-dismiss="modal">
                          Done
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-6 col-lg-1 d-flex align-items-end">
                  <button type="submit" class="search-btn-main">Search</button>
                </div>
              </div>
            </form>

            <style>
              .counter-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
              }

              .counter-control {
                display: flex;
                align-items: center;
                gap: 10px;
              }

              .counter-btn {
                width: 30px;
                height: 30px;
                border-radius: 50%;
                border: 1px solid #ddd;
                background: white;
                font-size: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                padding: 0;
              }

              .counter-btn:hover {
                background-color: #f8f9fa;
              }

              .counter-value {
                min-width: 20px;
                text-align: center;
                font-weight: 500;
              }
            </style>

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
                const checkinInput = document.getElementById('checkin');
                const checkoutInput = document.getElementById('checkout');
                const todayFormatted = formatDate(today);
                checkinInput.setAttribute('min', todayFormatted);
                checkoutInput.setAttribute('min', formatDate(tomorrow));
                checkinInput.value = todayFormatted;
                checkoutInput.value = formatDate(tomorrow);
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
                const guestSelector = document.getElementById("guest-selector");
                const counters = {
                  adults: { value: 2, min: 1, element: document.querySelector(".counter-item:nth-child(1) .counter-value") },
                  children: { value: 0, min: 0, element: document.querySelector(".counter-item:nth-child(2) .counter-value") },
                  rooms: { value: 1, min: 1, element: document.querySelector(".counter-item:nth-child(3) .counter-value") },
                };
                document.querySelectorAll(".counter-btn").forEach(btn => {
                  btn.addEventListener("click", function () {
                    const isIncrement = this.textContent === "+";
                    const counterItem = this.closest(".counter-item");
                    const label = counterItem.querySelector("label").textContent.toLowerCase();
                    const counter = counters[label];

                    if (isIncrement) counter.value++;
                    else if (counter.value > counter.min) counter.value--;

                    counter.element.textContent = counter.value;
                    updateGuestText();
                  });
                });

                function updateGuestText() {
                  const text = `${counters.adults.value} Adult${counters.adults.value !== 1 ? "s" : ""}, ` +
                    `${counters.children.value} Child${counters.children.value !== 1 ? "ren" : ""}, ` +
                    `${counters.rooms.value} Room${counters.rooms.value !== 1 ? "s" : ""}`;
                  guestSelector.value = text;
                  document.getElementById('adults').value = counters.adults.value;
                  document.getElementById('children').value = counters.children.value;
                  document.getElementById('rooms').value = counters.rooms.value;
                }

                document.getElementById("guestModal").addEventListener("hidden.bs.modal", updateGuestText);
              });
            </script>
          </div>
        </div>
      </div>

      <!-- Top Hotels section -->
      <div class="row mb-5">
        <div class="col-12">
          <h5 class="section-title-boxed">Top Hotels</h5>
          <div class="row">
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="hotel-card-compact">
                <img src="images/img10.jpg" class="hotel-img-compact" alt="Riverside Inn" />
                <div class="p-3">
                  <h6 class="mb-1">Riverside Inn</h6>
                  <p class="text-muted small mb-2">Jamshedpur</p>
                  <div class="d-flex align-items-center">
                    <span class="text-warning small">
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star-half-alt"></i>
                    </span>
                    <span class="text-muted small ms-2">235 reviews</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Hotel 2 -->
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="hotel-card-compact">
                <img src="images/img11.jpg" class="hotel-img-compact" alt="The Lindsay" />
                <div class="p-3">
                  <h6 class="mb-1">The Lindsay</h6>
                  <p class="text-muted small mb-2">Kolkata</p>
                  <div class="d-flex align-items-center">
                    <span class="text-warning small">
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="far fa-star"></i>
                    </span>
                    <span class="text-muted small ms-2">235 reviews</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Hotel 3 -->
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="hotel-card-compact">
                <img src="images/img12.jpg" class="hotel-img-compact" alt="Green Acre" />
                <div class="p-3">
                  <h6 class="mb-1">Green Acre</h6>
                  <p class="text-muted small mb-2">Ranchi</p>
                  <div class="d-flex align-items-center">
                    <span class="text-warning small">
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                    </span>
                    <span class="text-muted small ms-2">235 reviews</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Hotel 4 -->
            <div class="col-md-6 col-lg-3 mb-4">
              <div class="hotel-card-compact">
                <img src="images/img13.jpg" class="hotel-img-compact" alt="The Sonnet" />
                <div class="p-3">
                  <h6 class="mb-1">The Sonnet</h6>
                  <p class="text-muted small mb-2">Jamshedpur</p>
                  <div class="d-flex align-items-center">
                    <span class="text-warning small">
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star-half-alt"></i>
                    </span>
                    <span class="text-muted small ms-2">235 reviews</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Official Hotels -->
      <div class="mt-5">
        <h5 class="section-title-boxed">Official</h5>
        <div class="row">
          <div class="col-md-6 col-lg-3">
            <div class="hotel-card-compact">
              <img src="images/img15.jpg" class="hotel-img-compact" alt="The Sonnet" />
              <div class="p-3">
                <h6 class="mb-1">The Sonnet</h6>
                <p class="text-muted small mb-2">Jamshedpur</p>
                <div class="d-flex align-items-center">
                  <span class="text-warning small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                  </span>
                  <span class="text-muted small ms-2">235 reviews</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="hotel-card-compact">
              <img src="images/img13.jpg" class="hotel-img-compact" alt="The Sonnet" />
              <div class="p-3">
                <h6 class="mb-1">The Empire Hotel</h6>
                <p class="text-muted small mb-2">Jamshedpur</p>
                <div class="d-flex align-items-center">
                  <span class="text-warning small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                  </span>
                  <span class="text-muted small ms-2">235 reviews</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="hotel-card-compact">
              <img src="images/img10.jpg" class="hotel-img-compact" alt="The Sonnet" />
              <div class="p-3">
                <h6 class="mb-1">Hotel Hub</h6>
                <p class="text-muted small mb-2">Jamshedpur</p>
                <div class="d-flex align-items-center">
                  <span class="text-warning small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                  </span>
                  <span class="text-muted small ms-2">235 reviews</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="hotel-card-compact">
              <img src="images/img11.jpg" class="hotel-img-compact" alt="The Sonnet" />
              <div class="p-3">
                <h6 class="mb-1">Radisson Blu</h6>
                <p class="text-muted small mb-2">Ranchi</p>
                <div class="d-flex align-items-center">
                  <span class="text-warning small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                  </span>
                  <span class="text-muted small ms-2">235 reviews</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <br />
      <br />
      <br />
      <?php
 $destinations = [
    "Jamshedpur" => "jam.png",
    "Delhi" => "delhi.jpg",
    "Varanasi" => "varanasi.jpg",
    "Agra" => "agra.jpg",
    "Goa" => "goa.jpg",
    "Kolkata" => "kolkata.jpg",
    "Jaipur" => "jaipur.jpg",
    "Mumbai" => "mumbai.jpg",
    "Chennai" => "chennai.jpg",
    "Hyderabad" => "hyderabad.jpg",
    "Bengaluru" => "bengaluru.jpg",
    "Amritsar" => "amritsar.jpg"
];
 ?>

      <div class="mt-6">
        <h5 class="section-title-boxed">Popular destinations</h5>
        <div class="row">
          <?php foreach ($destinations as $destination =>
            $image): ?>
          <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="searchHotels.php?location=<?= urlencode($destination) ?>" class="text-decoration-none text-dark">
              <div class="destination-card-compact">
                <img src="images/<?= htmlspecialchars($image) ?>" class="destination-img-compact"
                  alt="<?= htmlspecialchars($destination) ?>" />
                <div class="destination-name-compact">
                  <?= htmlspecialchars($destination) ?>
                </div>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="mt-5">
        <h5 class="section-title-boxed">Deals for the weekend</h5>
        <div class="row">
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="hotel-card-compact deal-card">
              <img src="images/img13.jpg" alt="Radisson Blu" style="width: 100%; height: 200px; object-fit: cover" />

              <div class="p-3">
                <h6 class="mb-1">Radisson Blu</h6>
                <p class="text-muted small mb-2">Ranchi, Jharkhand</p>
                <div class="d-flex align-items-center mb-2">
                  <span class="text-warning small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                  </span>
                  <span class="text-muted small ms-2">235 reviews</span>
                </div>
                <div>
                  <span class="price-old small">Rs. 3500</span>
                  <span class="price-new">Rs. 2150 / Night</span>
                </div>
                <button class="btn btn-primary btn-sm w-100 mt-2">
                  Book Now
                </button>
              </div>
            </div>
          </div>

          <!-- Deal 2 -->
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="hotel-card-compact deal-card">
              <img src="images/img12.jpg" alt="Radisson Blu" style="width: 100%; height: 200px; object-fit: cover" />

              <div class="p-3">
                <h6 class="mb-1">Radisson Blu</h6>
                <p class="text-muted small mb-2">Ranchi, Jharkhand</p>
                <div class="d-flex align-items-center mb-2">
                  <span class="text-warning small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="far fa-star"></i>
                  </span>
                  <span class="text-muted small ms-2">198 reviews</span>
                </div>
                <div>
                  <span class="price-old small">Rs. 3500</span>
                  <span class="price-new">Rs. 2150 / Night</span>
                </div>
                <button class="btn btn-primary btn-sm w-100 mt-2">
                  Book Now
                </button>
              </div>
            </div>
          </div>

          <!-- Deal 3 -->
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="hotel-card-compact deal-card">
              <img src="images/img11.jpg" alt="Radisson Blu" style="width: 100%; height: 200px; object-fit: cover" />

              <div class="p-3">
                <h6 class="mb-1">Radisson Blu</h6>
                <p class="text-muted small mb-2">Ranchi, Jharkhand</p>
                <div class="d-flex align-items-center mb-2">
                  <span class="text-warning small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                  </span>
                  <span class="text-muted small ms-2">312 reviews</span>
                </div>
                <div>
                  <span class="price-old small">Rs. 3500</span>
                  <span class="price-new">Rs. 2150 / Night</span>
                </div>
                <button class="btn btn-primary btn-sm w-100 mt-2">
                  Book Now
                </button>
              </div>
            </div>
          </div>

          <!-- Deal 4 -->
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="hotel-card-compact deal-card">
              <img src="images/img10.jpg" alt="Radisson Blu" style="width: 100%; height: 200px; object-fit: cover" />

              <div class="p-3">
                <h6 class="mb-1">Radisson Blu</h6>
                <p class="text-muted small mb-2">Ranchi, Jharkhand</p>
                <div class="d-flex align-items-center mb-2">
                  <span class="text-warning small">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                  </span>
                  <span class="text-muted small ms-2">276 reviews</span>
                </div>
                <div>
                  <span class="price-old small">Rs. 3500</span>
                  <span class="price-new">Rs. 2150 / Night</span>
                </div>
                <button class="btn btn-primary btn-sm w-100 mt-2">
                  Book Now
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-5 text-center bg-light p-4 rounded" style="
            background: linear-gradient(125deg, #96aad900 0%, #1c469b 100%);
          ">
        <h5>Affordable places to stay in India!</h5>
        <p class="mb-0">TRIPSORUS</p>
      </div>
    </div>
  </div>
  <?php include 'footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const buttons = document.querySelectorAll(".user-type-btn");
    const userTypeInput = document.getElementById("userType");

    buttons.forEach((btn) => {
      btn.addEventListener("click", () => {
        buttons.forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");
        userTypeInput.value = btn.getAttribute("data-user-type");
      });
    });
  </script>
</body>

</html>