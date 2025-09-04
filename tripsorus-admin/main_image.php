<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Property Main Image - TRIPSORUS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #f8f9fa;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
            border-right: 1px solid #dee2e6;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border: none;
        }
        .property-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            object-fit: contain;
        }
        .property-info {
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
            color: white;
            border-radius: 10px;
            padding: 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5649c9, #8a82e8);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .image-container {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            transition: transform 0.3s;
            background-color: #f8f9fa;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .image-container:hover {
            transform: scale(1.02);
        }
        .loader {
            display: none;
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #6c5ce7;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .error-message {
            display: none;
            color: #e74c3c;
            background-color: #fde8e6;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #e74c3c;
        }
        .success-message {
            display: none;
            color: #2ecc71;
            background-color: #e8f8ef;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #2ecc71;
        }
        .property-details {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .detail-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .no-image {
            color: #6c757d;
            text-align: center;
            padding: 40px 20px;
        }
        .no-image i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <h4 class="text-center mb-4">TRIPSORUS Admin</h4>
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a href="#" class="nav-link"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            </li>
            <li class="nav-item mb-2">
                <a href="#" class="nav-link"><i class="fas fa-hotel me-2"></i> Properties</a>
            </li>
            <li class="nav-item mb-2">
                <a href="#" class="nav-link"><i class="fas fa-calendar-check me-2"></i> Bookings</a>
            </li>
            <li class="nav-item mb-2">
                <a href="#" class="nav-link"><i class="fas fa-users me-2"></i> Users</a>
            </li>
            <li class="nav-item mb-2">
                <a href="#" class="nav-link active"><i class="fas fa-image me-2"></i> Property Images</a>
            </li>
            <li class="nav-item mb-2">
                <a href="#" class="nav-link"><i class="fas fa-cog me-2"></i> Settings</a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-image me-2"></i> Find Property Main Image</h2>
                <a href="properties.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Properties
                </a>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card p-4 mb-4">
                        <h5 class="mb-3"><i class="fas fa-search me-2"></i> Search Property</h5>
                        <form id="searchForm" method="POST" action="">
                            <div class="mb-3">
                                <label for="propertyId" class="form-label">Property ID</label>
                                <input type="number" class="form-control" id="propertyId" name="propertyId" 
                                       placeholder="Enter Property ID" required 
                                       value="<?php echo isset($_POST['propertyId']) ? htmlspecialchars($_POST['propertyId']) : ''; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i> Find Property Image
                            </button>
                        </form>
                    </div>

                    <div class="loader" id="loader"></div>

                    <div class="error-message" id="errorMessage">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span id="errorText"></span>
                    </div>

                    <div class="success-message" id="successMessage">
                        <i class="fas fa-check-circle me-2"></i>
                        <span id="successText"></span>
                    </div>

                    <?php
                    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['propertyId'])) {
                        // Database connection
                        require_once '../db.php';
                        
                        $propertyId = intval($_POST['propertyId']);
                        
                        try {
                            // Query to get property details and main image
                            $query = "SELECT p.*, pi.image_path 
                                      FROM properties p 
                                      LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_main = 1 
                                      WHERE p.id = :propertyId";
                            
                            $stmt = $pdo->prepare($query);
                            $stmt->bindParam(':propertyId', $propertyId, PDO::PARAM_INT);
                            $stmt->execute();
                            
                            $property = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($property) {
                                echo '<div class="property-details">';
                                echo '<h5 class="mb-3"><i class="fas fa-info-circle me-2"></i> Property Details</h5>';
                                echo '<div class="detail-item">';
                                echo '<strong>Property ID:</strong> ' . htmlspecialchars($property['id']);
                                echo '</div>';
                                echo '<div class="detail-item">';
                                echo '<strong>Property Name:</strong> ' . htmlspecialchars($property['name']);
                                echo '</div>';
                                echo '<div class="detail-item">';
                                echo '<strong>Property Type:</strong> ' . htmlspecialchars($property['type']);
                                echo '</div>';
                                echo '<div class="detail-item">';
                                echo '<strong>Location:</strong> ' . htmlspecialchars($property['city']) . ', ' . htmlspecialchars($property['country']);
                                echo '</div>';
                                echo '<div class="detail-item">';
                                echo '<strong>Address:</strong> ' . htmlspecialchars($property['address']);
                                echo '</div>';
                                echo '</div>';
                                
                                // Store property data for JavaScript
                                echo '<script>var propertyData = ' . json_encode($property) . ';</script>';
                            } else {
                                echo '<div class="error-message" style="display: block;">';
                                echo '<i class="fas fa-exclamation-circle me-2"></i>';
                                echo 'Property not found with ID: ' . htmlspecialchars($propertyId);
                                echo '</div>';
                            }
                        } catch (PDOException $e) {
                            echo '<div class="error-message" style="display: block;">';
                            echo '<i class="fas fa-exclamation-circle me-2"></i>';
                            echo 'Database error: ' . htmlspecialchars($e->getMessage());
                            echo '</div>';
                        }
                    }
                    ?>
                </div>

                <div class="col-md-6">
                    <div class="card p-4">
                        <h5 class="mb-3"><i class="fas fa-camera me-2"></i> Main Property Image</h5>
                        <div id="imageResult">
                            <?php
                            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['propertyId'])) {
                                if ($property) {
                                    if ($property['image_path']) {
                                        echo '<div class="image-container">';
                                        echo '<img src="' . htmlspecialchars($property['image_path']) . '" ';
                                        echo 'alt="Property Main Image" class="property-image" ';
                                        echo 'onerror="this.style.display=\'none\'; document.getElementById(\'noImageMessage\').style.display=\'block\';">';
                                        echo '<div id="noImageMessage" class="no-image" style="display: none;">';
                                        echo '<i class="fas fa-exclamation-triangle"></i>';
                                        echo '<p>Image could not be loaded</p>';
                                        echo '<p class="small">Path: ' . htmlspecialchars($property['image_path']) . '</p>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '<div class="mt-3">';
                                        echo '<p class="mb-1"><strong>Image Path:</strong> ' . htmlspecialchars($property['image_path']) . '</p>';
                                        echo '</div>';
                                    } else {
                                        echo '<div class="no-image">';
                                        echo '<i class="fas fa-image"></i>';
                                        echo '<p>No main image found for this property</p>';
                                        echo '</div>';
                                    }
                                }
                            } else {
                                echo '<div class="text-center text-muted py-5">';
                                echo '<i class="fas fa-image fa-4x mb-3"></i>';
                                echo '<p>Enter a Property ID to view the main image</p>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.getElementById('searchForm');
            const loader = document.getElementById('loader');
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');
            const errorText = document.getElementById('errorText');
            const successText = document.getElementById('successText');

            // If we have property data from PHP, show success message
            if (typeof propertyData !== 'undefined') {
                if (propertyData.image_path) {
                    showSuccess('Property image found successfully!');
                } else {
                    showError('Property found but no main image available.');
                }
            }

            searchForm.addEventListener('submit', function() {
                // Show loader when form is submitted
                loader.style.display = 'block';
                hideMessages();
            });
            
            function showError(message) {
                errorMessage.style.display = 'block';
                errorText.textContent = message;
            }
            
            function showSuccess(message) {
                successMessage.style.display = 'block';
                successText.textContent = message;
            }
            
            function hideMessages() {
                errorMessage.style.display = 'none';
                successMessage.style.display = 'none';
            }
        });
    </script>
</body>
</html>