<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Property - TRIPSORUS Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles/style.css">
  <style>
    .room-type-card {
      background-color: #f8f9fa;
      border-radius: 5px;
      padding: 15px;
      margin-bottom: 15px;
      border: 1px solid #dee2e6;
      position: relative;
    }

    .thumbnail-container {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 10px;
      min-height: 50px;
    }

    .thumbnail-container.empty::after {
      content: "No images uploaded yet";
      color: #6c757d;
      font-style: italic;
      width: 100%;
      padding: 10px;
    }

    .thumbnail-wrapper {
      position: relative;
      width: 80px;
      height: 80px;
    }

    .thumbnail-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border: 1px solid #ddd;
      border-radius: 4px;
    }

    .remove-thumbnail {
      position: absolute;
      top: -5px;
      right: -5px;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: #dc3545;
      color: white;
      border: none;
      font-size: 10px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .remove-room-type {
      position: absolute;
      top: 4px;
      right: 16px;
      font-size: 9px;
      background-color: #eb2020;
    }

    .file-upload-hint {
      font-size: 0.8rem;
      color: #6c757d;
      margin-top: 5px;
    }

    .room-thumbnails-container {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 10px;
    }

    .upload-status {
      margin-top: 10px;
      font-size: 0.9rem;
      color: #28a745;
    }

    .auth-alert {
      display: none;
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1050;
      max-width: 400px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .session-check {
      display: none;
    }

    .main-image-label {
      font-weight: bold;
      color: #0d6efd;
    }

    .main-image-preview {
      border: 2px solid #0d6efd;
    }

    .room-image-management {
      margin-top: 15px;
      border-top: 1px solid #eee;
      padding-top: 15px;
    }

    .image-upload-section {
      margin-bottom: 20px;
      padding: 15px;
      background-color: #f8f9fa;
      border-radius: 5px;
    }
  </style>
</head>

<body>
  <div class="alert alert-danger auth-alert" id="authAlert" role="alert">
    <h4 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Authentication Required</h4>
    <p>Your session may have expired or you don't have sufficient permissions to add properties.</p>
    <hr>
    <p class="mb-0">Please <a href="login.php" class="alert-link">log in again</a> or contact your administrator.</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <div class="session-check" id="sessionCheck">
  </div>
  <!-- Sidebar Navigation -->
  <?php include 'sidebar.php'; ?>
  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Add New Property</h2>
        <a href="properties.php" class="btn btn-outline-secondary">
          <i class="fas fa-arrow-left me-2"></i> Back to Properties
        </a>
      </div>
      <!-- Property Form -->
      <div class="card p-4">
        <form id="propertyForm" action="add-property.php" method="POST" enctype="multipart/form-data">
          <!-- Basic Info -->
          <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i> Basic Information</h5>
          <div class="row mb-4">
            <div class="col-md-6 mb-3">
              <label for="propertyName" class="form-label">Property Name*</label>
              <input type="text" class="form-control" id="propertyName" name="property_name"
                placeholder="e.g., The Grand Plaza Hotel" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="propertyType" class="form-label">Property Type*</label>
              <select class="form-select" id="propertyType" name="property_type" required>
                <option value="">Select Type</option>
                <option value="hotel">Hotel</option>
                <option value="resort">Resort</option>
                <option value="villa">Villa</option>
                <option value="apartment">Apartment</option>
                <option value="guesthouse">Guest House</option>
              </select>
            </div>
            <div class="col-md-12 mb-3">
              <label for="propertyDescription" class="form-label">Description*</label>
              <textarea class="form-control" id="propertyDescription" name="description" rows="4"
                placeholder="Describe the property..." required></textarea>
            </div>
          </div>
          <!-- Location -->
          <h5 class="mb-3"><i class="fas fa-map-marker-alt me-2"></i> Location</h5>
          <div class="row mb-4">
            <div class="col-md-6 mb-3">
              <label for="propertyAddress" class="form-label">Address*</label>
              <input type="text" class="form-control" id="propertyAddress" name="address" placeholder="Street, Landmark"
                required>
            </div>
            <div class="col-md-3 mb-3">
              <label for="propertyCity" class="form-label">City*</label>
              <input type="text" class="form-control" id="propertyCity" name="city" placeholder="e.g., Kolkata"
                required>
            </div>
            <div class="col-md-3 mb-3">
              <label for="propertyCountry" class="form-label">Country*</label>
              <input type="text" class="form-control" id="propertyCountry" name="country" placeholder="e.g., India"
                required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="propertyLocation" class="form-label">Google Maps Link (Optional)</label>
              <input type="url" class="form-control" id="propertyLocation" name="map_link"
                placeholder="https://maps.google.com/...">
            </div>
            <!-- Check-In / Check-Out Section -->
            <h5 class="mb-3"><i class="fas fa-clock me-2"></i> Property Timings</h5>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="check_in" class="form-label">Check-In Time*</label>
                <input type="time" class="form-control" id="check_in" name="check_in" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="check_out" class="form-label">Check-Out Time*</label>
                <input type="time" class="form-control" id="check_out" name="check_out" required>
              </div>
            </div>
          </div>
          <!-- Room Types -->
          <h5 class="mb-3"><i class="fas fa-bed me-2"></i> Room Types & Configuration</h5>
          <div id="roomTypesContainer">
            <div class="room-type-card">
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Room Type*</label>
                  <input type="text" class="form-control" name="room_types[0][name]" placeholder="e.g., Deluxe Room"
                    required>
                </div>
                <div class="col-md-2 mb-3">
                  <label class="form-label">Max Guests*</label>
                  <input type="number" class="form-control" name="room_types[0][max_guests]" min="1" value="2" required>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Bed Size*</label>
                  <select class="form-select" name="room_types[0][bed_size]" required>
                    <option value="">Select Bed Size</option>
                    <option value="Queen Bed">Queen Bed</option>
                    <option value="King Bed">King Bed</option>
                    <option value="Double Bed">Double Bed</option>
                    <option value="Twin Beds">Twin Beds</option>
                  </select>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label">Quantity Available*</label>
                  <input type="number" class="form-control" name="room_types[0][quantity]" min="1" value="5" required>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Base Price (₹ per night)*</label>
                  <input type="number" class="form-control" name="room_types[0][price]" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Discount Price (Optional)</label>
                  <input type="number" class="form-control" name="room_types[0][discount_price]">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">Size (sq ft)</label>
                  <input type="number" class="form-control" name="room_types[0][size]">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Room Description</label>
                <textarea class="form-control" name="room_types[0][description]" rows="2"
                  placeholder="Describe this room type..."></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Room Amenities</label>
                <div class="amenities-checkboxes">
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="room_types[0][amenities][]" value="AC"
                      id="room0_ac">
                    <label class="form-check-label" for="room0_ac">Air Conditioning</label>
                  </div>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="room_types[0][amenities][]" value="TV"
                      id="room0_tv">
                    <label class="form-check-label" for="room0_tv">TV</label>
                  </div>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="room_types[0][amenities][]" value="Minibar"
                      id="room0_minibar">
                    <label class="form-check-label" for="room0_minibar">Minibar</label>
                  </div>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="room_types[0][amenities][]" value="Safe"
                      id="room0_safe">
                    <label class="form-check-label" for="room0_safe">Safe</label>
                  </div>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="room_types[0][amenities][]" value="Balcony"
                      id="room0_balcony">
                    <label class="form-check-label" for="room0_balcony">Balcony</label>
                  </div>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="room_types[0][amenities][]" value="Desk"
                      id="room0_desk">
                    <label class="form-check-label" for="room0_desk">Work Desk</label>
                  </div>
                </div>
              </div>
              <!-- Room Main Image -->
              <div class="mb-3">
                <label class="form-label main-image-label">Room Main Image*</label>
                <input class="form-control" type="file" name="room_types[0][main_image]" accept="image/*" required>
                <div class="file-upload-hint">This will be the primary image for this room type (JPEG, PNG, max 5MB)
                </div>
                <div class="main-image-preview-container mt-2"></div>
              </div>

              <div class="room-image-management">
                <label class="form-label">Additional Room Images (Max 5)</label>
                <input class="form-control room-additional-images" type="file" name="room_types[0][images][]" multiple
                  accept="image/*">
                <div class="file-upload-hint">Upload additional images of this room type (JPEG, PNG, max 5MB each)</div>
                <div class="room-thumbnails-container"></div>
              </div>
            </div>
          </div>
          <button type="button" class="btn btn-outline-primary mb-4" id="addRoomType">
            <i class="fas fa-plus me-2"></i> Add Another Room Type
          </button>
          <!-- Property Amenities -->
          <h5 class="mb-3"><i class="fas fa-star me-2"></i> Property Amenities</h5>
          <div class="row mb-4">
            <div class="col-md-3">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="wifi" name="amenities[]" value="Free WiFi">
                <label class="form-check-label" for="wifi">Free WiFi</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="pool" name="amenities[]" value="Swimming Pool">
                <label class="form-check-label" for="pool">Swimming Pool</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="parking" name="amenities[]" value="Free Parking">
                <label class="form-check-label" for="parking">Free Parking</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="solo" name="amenities[]" value="Solo">
                <label class="form-check-label" for="solo">Solo</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="ac" name="amenities[]" value="Air Conditioning">
                <label class="form-check-label" for="ac">Air Conditioning</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="restaurant" name="amenities[]" value="Restaurant">
                <label class="form-check-label" for="restaurant">Restaurant</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="gym" name="amenities[]" value="Gym">
                <label class="form-check-label" for="gym">Gym</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="corporate" name="amenities[]" value="Corporate">
                <label class="form-check-label" for="corporate">Corporate</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="breakfast" name="amenities[]"
                  value="Breakfast Included">
                <label class="form-check-label" for="breakfast">Breakfast Included</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="spa" name="amenities[]" value="Spa">
                <label class="form-check-label" for="spa">Spa</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="bar" name="amenities[]" value="Bar">
                <label class="form-check-label" for="bar">Bar</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="single_lady" name="amenities[]" value="Single Lady">
                <label class="form-check-label" for="single_lady">Single Lady</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="pets" name="amenities[]" value="Pets Allowed">
                <label class="form-check-label" for="pets">Pets Allowed</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="laundry" name="amenities[]" value="Laundry">
                <label class="form-check-label" for="laundry">Laundry</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="concierge" name="amenities[]"
                  value="24/7 Concierge">
                <label class="form-check-label" for="concierge">24/7 Concierge</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="couple" name="amenities[]" value="Couple">
                <label class="form-check-label" for="couple">Couple</label>
              </div>
            </div>
          </div>
          <div class="row mb-4">
            <div class="col-md-3">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="student" name="amenities[]" value="Student">
                <label class="form-check-label" for="student">Student</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="budget" name="amenities[]" value="Budget Filter">
                <label class="form-check-label" for="budget">Budget Filter</label>
              </div>
            </div>
          </div>
          <!-- Property Main Image -->
          <h5 class="mb-3"><i class="fas fa-image me-2"></i> Property Main Image</h5>
          <div class="mb-4">
            <label for="propertyMainImage" class="form-label main-image-label">Main Property Image*</label>
            <input class="form-control" type="file" id="propertyMainImage" name="property_main_image" accept="image/*"
              required>
            <div class="file-upload-hint">This will be the primary image for the property (JPEG, PNG, max 5MB)</div>
            <div class="main-image-preview-container mt-2" id="propertyMainImagePreview"></div>
          </div>
          <h5 class="mb-3"><i class="fas fa-images me-2"></i> Additional Property Images</h5>
          <div class="mb-4">
            <label for="propertyImages" class="form-label">Upload Additional Images (Max 10)</label>
            <input class="form-control" type="file" id="propertyImages" name="propertyImages[]" multiple
              accept="image/*">
            <div class="file-upload-hint">Upload additional property images (JPEG, PNG, max 5MB each)</div>
            <div class="thumbnail-container empty" id="thumbnailsContainer"></div>
            <div class="upload-status" id="uploadStatus"></div>
          </div>
          <!-- Submit Button -->
          <div class="d-flex justify-content-end">
            <button type="reset" class="btn btn-outline-danger me-3" id="resetButton">Clear Form</button>
            <button type="submit" class="btn btn-primary px-4" id="submitButton">
              <i class="fas fa-save me-2"></i> Save Property
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      let currentPropertyFiles = [];
      const imageInput = document.getElementById('propertyImages');
      const thumbnailsContainer = document.getElementById('thumbnailsContainer');
      const uploadStatus = document.getElementById('uploadStatus');
      let roomTypeCount = 1;
      document.getElementById('addRoomType').addEventListener('click', function () {
        const container = document.getElementById('roomTypesContainer');
        const newRoomType = document.createElement('div');
        newRoomType.className = 'room-type-card mt-3';
        newRoomType.innerHTML = `
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Room Type*</label>
              <input type="text" class="form-control" name="room_types[${roomTypeCount}][name]" placeholder="e.g., Deluxe Room" required>
            </div>
            <div class="col-md-2 mb-3">
              <label class="form-label">Max Guests*</label>
              <input type="number" class="form-control" name="room_types[${roomTypeCount}][max_guests]" min="1" value="2" required>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Bed Size*</label>
              <select class="form-select" name="room_types[${roomTypeCount}][bed_size]" required>
                <option value="">Select Bed Size</option>
                <option value="Queen Bed">Queen Bed</option>
                <option value="King Bed">King Bed</option>
                <option value="Double Bed">Double Bed</option>
                <option value="Twin Beds">Twin Beds</option>
              </select>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Quantity Available*</label>
              <input type="number" class="form-control" name="room_types[${roomTypeCount}][quantity]" min="1" value="5" required>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Base Price (₹ per night)*</label>
              <input type="number" class="form-control" name="room_types[${roomTypeCount}][price]" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Discount Price (Optional)</label>
              <input type="number" class="form-control" name="room_types[${roomTypeCount}][discount_price]">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Size (sq ft)</label>
              <input type="number" class="form-control" name="room_types[${roomTypeCount}][size]">
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Room Description</label>
            <textarea class="form-control" name="room_types[${roomTypeCount}][description]" rows="2" placeholder="Describe this room type..."></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Room Amenities</label>
            <div class="amenities-checkboxes">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="room_types[${roomTypeCount}][amenities][]" value="AC" id="room${roomTypeCount}_ac">
                <label class="form-check-label" for="room${roomTypeCount}_ac">Air Conditioning</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="room_types[${roomTypeCount}][amenities][]" value="TV" id="room${roomTypeCount}_tv">
                <label class="form-check-label" for="room${roomTypeCount}_tv">TV</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="room_types[${roomTypeCount}][amenities][]" value="Minibar" id="room${roomTypeCount}_minibar">
                <label class="form-check-label" for="room${roomTypeCount}_minibar">Minibar</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="room_types[${roomTypeCount}][amenities][]" value="Safe" id="room${roomTypeCount}_safe">
                <label class="form-check-label" for="room${roomTypeCount}_safe">Safe</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="room_types[${roomTypeCount}][amenities][]" value="Balcony" id="room${roomTypeCount}_balcony">
                <label class="form-check-label" for="room${roomTypeCount}_balcony">Balcony</label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="room_types[${roomTypeCount}][amenities][]" value="Desk" id="room${roomTypeCount}_desk">
                <label class="form-check-label" for="room${roomTypeCount}_desk">Work Desk</label>
              </div>
            </div>
          </div>
          
          <!-- Room Main Image -->
          <div class="mb-3">
            <label class="form-label main-image-label">Room Main Image*</label>
            <input class="form-control" type="file" name="room_types[${roomTypeCount}][main_image]" accept="image/*" required>
            <div class="file-upload-hint">This will be the primary image for this room type (JPEG, PNG, max 5MB)</div>
            <div class="main-image-preview-container mt-2"></div>
          </div>
          
          <div class="room-image-management">
            <label class="form-label">Additional Room Images (Max 5)</label>
            <input class="form-control room-additional-images" type="file" name="room_types[${roomTypeCount}][images][]" multiple accept="image/*">
            <div class="file-upload-hint">Upload additional images of this room type (JPEG, PNG, max 5MB each)</div>
            <div class="room-thumbnails-container"></div>
          </div>
          
          <button type="button" class="btn btn-outline-danger btn-sm remove-room-type">
            <i class="fas fa-trash me-1"></i> Remove Room Type
          </button>
        `;
        container.appendChild(newRoomType);
        roomTypeCount++;
        newRoomType.querySelector('.remove-room-type').addEventListener('click', function () {
          container.removeChild(newRoomType);
        });
        setupRoomImagePreviewsForCard(newRoomType);
        setupMainImagePreviewForCard(newRoomType);
      });
      function setupRoomImagePreviewsForCard(roomCard) {
        const input = roomCard.querySelector('.room-additional-images');
        const previewContainer = roomCard.querySelector('.room-thumbnails-container');
        let currentFiles = input.files ? Array.from(input.files) : [];
        input.addEventListener('change', function (e) {
          const newFiles = Array.from(this.files);
          const validFiles = newFiles.filter(file => {
            if (!file.type.startsWith('image/')) {
              alert(`File ${file.name} is not an image and will be skipped`);
              return false;
            }

            if (file.size > 5 * 1024 * 1024) {
              alert(`File ${file.name} is too large (max 5MB) and will be skipped`);
              return false;
            }

            return true;
          });
          const allFiles = [...currentFiles, ...validFiles].slice(0, 5);
          const dataTransfer = new DataTransfer();
          allFiles.forEach(file => dataTransfer.items.add(file));
          input.files = dataTransfer.files;
          currentFiles = allFiles;
          renderRoomThumbnails(previewContainer, input, currentFiles);
        });
        if (currentFiles.length > 0) {
          renderRoomThumbnails(previewContainer, input, currentFiles);
        }
      }
      function renderRoomThumbnails(previewContainer, input, files) {
        previewContainer.innerHTML = '';

        if (files.length === 0) {
          previewContainer.classList.add('empty');
          return;
        }

        previewContainer.classList.remove('empty');

        files.forEach((file, index) => {
          const reader = new FileReader();
          reader.onload = function (e) {
            const thumbnailWrapper = document.createElement('div');
            thumbnailWrapper.className = 'thumbnail-wrapper';

            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'thumbnail-img';

            const removeBtn = document.createElement('button');
            removeBtn.className = 'remove-thumbnail';
            removeBtn.innerHTML = '×';
            removeBtn.addEventListener('click', function () {
              files.splice(index, 1);
              const dataTransfer = new DataTransfer();
              files.forEach(file => dataTransfer.items.add(file));
              input.files = dataTransfer.files;
              renderRoomThumbnails(previewContainer, input, files);
            });

            thumbnailWrapper.appendChild(img);
            thumbnailWrapper.appendChild(removeBtn);
            previewContainer.appendChild(thumbnailWrapper);
          };
          reader.readAsDataURL(file);
        });
      }

      // Function to handle main image previews for a specific card
      function setupMainImagePreviewForCard(roomCard) {
        const input = roomCard.querySelector('input[name$="[main_image]"]');
        const previewContainer = roomCard.querySelector('.main-image-preview-container');

        input.addEventListener('change', function () {
          if (this.files && this.files[0]) {
            const file = this.files[0];

            // Validate file type and size
            if (!file.type.startsWith('image/')) {
              alert(`File ${file.name} is not an image`);
              this.value = '';
              return;
            }

            if (file.size > 5 * 1024 * 1024) {
              alert(`File ${file.name} is too large (max 5MB)`);
              this.value = '';
              return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
              previewContainer.innerHTML = `
                <div class="thumbnail-wrapper">
                  <img src="${e.target.result}" class="thumbnail-img main-image-preview">
                  <button type="button" class="remove-thumbnail remove-room-main-image">×</button>
                </div>
              `;

              // Add event listener to remove button
              previewContainer.querySelector('.remove-room-main-image').addEventListener('click', function () {
                previewContainer.innerHTML = '';
                input.value = '';
              });
            };
            reader.readAsDataURL(file);
          }
        });
      }

      // Initialize image previews for the first room type
      const firstRoomCard = document.querySelector('.room-type-card');
      if (firstRoomCard) {
        setupRoomImagePreviewsForCard(firstRoomCard);
        setupMainImagePreviewForCard(firstRoomCard);
      }

      // Property Main Image Preview
      const propertyMainImageInput = document.getElementById('propertyMainImage');
      const propertyMainImagePreview = document.getElementById('propertyMainImagePreview');

      propertyMainImageInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
          const file = this.files[0];

          // Validate file type and size
          if (!file.type.startsWith('image/')) {
            alert(`File ${file.name} is not an image`);
            this.value = '';
            return;
          }

          if (file.size > 5 * 1024 * 1024) {
            alert(`File ${file.name} is too large (max 5MB)`);
            this.value = '';
            return;
          }

          const reader = new FileReader();
          reader.onload = function (e) {
            propertyMainImagePreview.innerHTML = `
              <div class="thumbnail-wrapper">
                <img src="${e.target.result}" class="thumbnail-img main-image-preview">
                <button type="button" class="remove-thumbnail" id="removePropertyMainImage">×</button>
              </div>
            `;
            document.getElementById('removePropertyMainImage').addEventListener('click', function () {
              propertyMainImagePreview.innerHTML = '';
              propertyMainImageInput.value = '';
            });
          };
          reader.readAsDataURL(file);
        }
      });
      imageInput.addEventListener('change', function (e) {
        const newFiles = Array.from(this.files);
        const validFiles = newFiles.filter(file => {
          if (!file.type.startsWith('image/')) {
            alert(`File ${file.name} is not an image and will be skipped`);
            return false;
          }

          if (file.size > 5 * 1024 * 1024) {
            alert(`File ${file.name} is too large (max 5MB) and will be skipped`);
            return false;
          }

          return true;
        });
        const allFiles = [...currentPropertyFiles, ...validFiles].slice(0, 10);
        const dataTransfer = new DataTransfer();
        allFiles.forEach(file => dataTransfer.items.add(file));
        imageInput.files = dataTransfer.files;
        currentPropertyFiles = allFiles;
        renderPropertyThumbnails();
        uploadStatus.textContent = `Added ${validFiles.length} image(s). Total: ${currentPropertyFiles.length}/10`;
        uploadStatus.style.color = "#28a745";
        setTimeout(() => {
          uploadStatus.textContent = "";
        }, 2000);
      });

      function renderPropertyThumbnails() {
        thumbnailsContainer.innerHTML = '';

        if (currentPropertyFiles.length === 0) {
          thumbnailsContainer.classList.add('empty');
          return;
        }

        thumbnailsContainer.classList.remove('empty');
        currentPropertyFiles.forEach((file, index) => {
          const reader = new FileReader();
          reader.onload = function (e) {
            const thumbnailWrapper = document.createElement('div');
            thumbnailWrapper.className = 'thumbnail-wrapper';

            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'thumbnail-img';
            img.alt = 'Preview';

            const removeBtn = document.createElement('button');
            removeBtn.className = 'remove-thumbnail';
            removeBtn.innerHTML = '×';
            removeBtn.title = 'Remove this image';
            removeBtn.dataset.index = index;

            removeBtn.addEventListener('click', (event) => {
              event.preventDefault();
              currentPropertyFiles.splice(index, 1);
              const dataTransfer = new DataTransfer();
              currentPropertyFiles.forEach(file => dataTransfer.items.add(file));
              imageInput.files = dataTransfer.files;
              renderPropertyThumbnails();
              uploadStatus.textContent = `Removed 1 image. Total: ${currentPropertyFiles.length}/10`;
              uploadStatus.style.color = "#dc3545";
              setTimeout(() => {
                uploadStatus.textContent = "";
              }, 2000);
            });

            thumbnailWrapper.appendChild(img);
            thumbnailWrapper.appendChild(removeBtn);
            thumbnailsContainer.appendChild(thumbnailWrapper);
          };
          reader.readAsDataURL(file);
        });
      }

      // Initial render
      renderPropertyThumbnails();
      document.getElementById('resetButton').addEventListener('click', function () {
        currentPropertyFiles = [];
        const dataTransfer = new DataTransfer();
        imageInput.files = dataTransfer.files;
        renderPropertyThumbnails();
        document.getElementById('propertyMainImagePreview').innerHTML = '';
        document.querySelectorAll('.main-image-preview-container').forEach(container => {
          container.innerHTML = '';
        });
        document.querySelectorAll('.room-thumbnails-container').forEach(container => {
          container.innerHTML = '';
          container.classList.add('empty');
        });

        uploadStatus.textContent = "All images cleared";
        uploadStatus.style.color = "#dc3545";
        setTimeout(() => {
          uploadStatus.textContent = "";
        }, 2000);
      });

      // Form validation before submission
      document.getElementById('propertyForm').addEventListener('submit', function (e) {
        const roomTypes = document.querySelectorAll('.room-type-card');
        if (roomTypes.length === 0) {
          e.preventDefault();
          alert('Please add at least one room type');
          return;
        }

        // Validate property main image exists
        const propertyMainImage = document.getElementById('propertyMainImage');
        if (propertyMainImage.files.length === 0) {
          e.preventDefault();
          alert('Please upload a main image for the property');
          return;
        }

        // Validate room main images exist
        let missingRoomMainImages = false;
        document.querySelectorAll('input[name$="[main_image]"]').forEach(input => {
          if (input.files.length === 0) {
            missingRoomMainImages = true;
          }
        });

        if (missingRoomMainImages) {
          e.preventDefault();
          alert('Please upload a main image for each room type');
          return;
        }
        if (imageInput.files.length === 0) {
          if (!confirm('You haven\'t uploaded any additional property images. Continue anyway?')) {
            e.preventDefault();
            return;
          }
        }
        let missingRoomImages = false;
        document.querySelectorAll('.room-type-card').forEach(card => {
          const fileInput = card.querySelector('input[name$="[images][]"]');
          if (fileInput.files.length === 0) {
            missingRoomImages = true;
          }
        });

        if (missingRoomImages) {
          if (!confirm('Some room types don\'t have additional images. Continue anyway?')) {
            e.preventDefault();
          }
        }
      });
      function checkAuthentication() {
        fetch('check_session.php', {
          method: 'GET',
          credentials: 'same-origin'
        })
          .then(response => {
            if (!response.ok) {
              throw new Error('Authentication failed');
            }
            return response.json();
          })
          .then(data => {
            if (!data.authenticated || !data.isAdmin) {
              showAuthError();
            }
          })
          .catch(error => {
            console.error('Authentication check failed:', error);
          });
      }
      function showAuthError() {
        const authAlert = document.getElementById('authAlert');
        authAlert.style.display = 'block';
        document.getElementById('submitButton').disabled = true;
      }
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('error') === '403') {
        showAuthError();
      }
    });
  </script>
</body>

</html>