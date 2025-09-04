<?php
session_start();

$timeout_duration = 900; 

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();     
    session_destroy();   
    echo "<script>
        alert('Session expired due to inactivity. Please login again.');
        window.location.href = 'index.php';
    </script>";
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time(); 


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'super_admin') {
    echo "<script>
        alert('Access denied. Super Admins only.');
        window.location.href = 'index.php';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRIPSORUS - Super Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            overflow-x: hidden;
        }

        .sidebar {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 250px;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 15px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 500;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .admin-card {
            transition: all 0.3s;
            border-left: 4px solid var(--primary-color);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .badge-admin {
            background-color: #6f42c1;
        }

        .badge-property {
            background-color: #20c997;
        }

        .property-card {
            transition: all 0.3s;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .property-img {
            height: 150px;
            object-fit: cover;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .main-content {
            margin-left: 250px;
            transition: all 0.3s;
        }

        /* Mobile styles */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }

            .sidebar .nav-link span {
                display: none;
            }

            .sidebar .nav-link i {
                margin-right: 0;
                font-size: 1.2rem;
            }

            .sidebar h4 {
                font-size: 1rem;
                white-space: nowrap;
            }

            .main-content {
                margin-left: 70px;
            }

            .icon-circle {
                width: 50px;
                height: 50px;
            }

            .icon-circle i {
                font-size: 1.5rem !important;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                position: fixed;
                z-index: 1000;
            }

            .sidebar.active {
                width: 250px;
            }

            .sidebar.active .nav-link span {
                display: inline;
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block !important;
            }
        }

        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1100;
        }

        
        .admin-actions .btn {
            margin-bottom: 5px;
            width: 100%;
        }

        @media (min-width: 768px) {
            .admin-actions .btn {
                width: auto;
                margin-bottom: 0;
            }
        }

        
        @media (max-width: 576px) {
            .modal-dialog {
                margin: 0.5rem auto;
            }
        }

    
        .badge-active {
            background-color: #28a745;
        }

        .badge-inactive {
            background-color: #dc3545;
        }

        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }

        /* Loading spinner */
        .spinner-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>

<body>
    <!-- Loading Spinner -->
    <div class="spinner-container" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Mobile Menu Button -->
    <button class="btn btn-primary mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 sidebar" id="sidebar">
                <div class="position-sticky pt-3">
                    <h4 class="text-white text-center mb-4">
                        <i class="fas fa-crown me-2"></i><span class="sidebar-text">Super Admin</span>
                    </h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i><span class="sidebar-text">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#properties">
                                <i class="fas fa-hotel me-2"></i><span class="sidebar-text">All Properties</span>
                            </a>
                        </li>
                        <!-- <li class="nav-item">
                            <a class="nav-link" href="#admins">
                                <i class="fas fa-users-cog me-2"></i><span class="sidebar-text">Manage Admins</span>
                            </a>
                        </li> -->
                        <li class="nav-item">
                            <a class="nav-link" href="#reports">
                                <i class="fas fa-chart-bar me-2"></i><span class="sidebar-text">Reports</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#settings">
                                <i class="fas fa-cog me-2"></i><span class="sidebar-text">Settings</span>
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="#logout">
                                <i class="fas fa-sign-out-alt me-2"></i><span class="sidebar-text">Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
                <!-- Dashboard Section -->
                <section id="dashboard">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Admin Dashboard</h2>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                id="dashboardDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-calendar me-2"></i> Last 30 Days
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Today</a></li>
                                <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                                <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                                <li><a class="dropdown-item" href="#">This Month</a></li>
                                <li><a class="dropdown-item" href="#">Custom Range</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card admin-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title text-muted">Total Properties</h6>
                                            <h2 class="mb-0">147</h2>
                                            <small class="text-success"><i class="fas fa-arrow-up me-1"></i> 12% from
                                                last month</small>
                                        </div>
                                        <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                                            <i class="fas fa-hotel fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card admin-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title text-muted">Active Admins</h6>
                                            <h2 class="mb-0">8</h2>
                                            <small class="text-success"><i class="fas fa-arrow-up me-1"></i> 2
                                                new</small>
                                        </div>
                                        <div class="icon-circle bg-success bg-opacity-10 text-success">
                                            <i class="fas fa-users-cog fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card admin-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title text-muted">Monthly Revenue</h6>
                                            <h2 class="mb-0">₹2.45L</h2>
                                            <small class="text-success"><i class="fas fa-arrow-up me-1"></i> 18%
                                                growth</small>
                                        </div>
                                        <div class="icon-circle bg-warning bg-opacity-10 text-warning">
                                            <i class="fas fa-rupee-sign fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card admin-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title text-muted">Bookings</h6>
                                            <h2 class="mb-0">89</h2>
                                            <small class="text-danger"><i class="fas fa-arrow-down me-1"></i> 5% from
                                                last month</small>
                                        </div>
                                        <div class="icon-circle bg-info bg-opacity-10 text-info">
                                            <i class="fas fa-calendar-check fa-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Chart -->
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Revenue Analytics</h5>
                            <div>
                                <button class="btn btn-sm btn-outline-secondary">Monthly</button>
                                <button class="btn btn-sm btn-outline-secondary">Quarterly</button>
                                <button class="btn btn-sm btn-primary">Yearly</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Management Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Admin Management</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                                <i class="fas fa-plus me-2"></i> Add New Admin
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Last Active</th>
                                            <th>Status</th>
                                            <th class="admin-actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="adminTableBody">
                                        <!-- Admin data will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Properties -->
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recently Added Properties</h5>
                            <a href="#properties" class="btn btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="row" id="recentProperties">
                                <!-- Recent properties will be loaded here -->
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Properties Section -->
                <section id="properties" class="py-5 d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>All Properties</h2>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
                            <i class="fas fa-plus me-2"></i> Add Property
                        </a>
                    </div>

                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Property Management</h5>
                            <div class="input-group" style="width: 300px;">
                                <input type="text" class="form-control" id="propertySearch"
                                    placeholder="Search properties...">
                                <button class="btn btn-outline-secondary" type="button" id="searchPropertyBtn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Property</th>
                                            <th>Type</th>
                                            <th>Location</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Admin</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="propertyTableBody">
                                        <!-- Property data will be loaded here -->
                                    </tbody>
                                </table>
                            </div>

                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center" id="propertyPagination">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </section>

                <!-- Reports Section -->
                <section id="reports" class="py-5 d-none">
                    <h2 class="mb-4">Reports & Analytics</h2>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Booking Trends</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="bookingTrendsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Property Types Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="propertyTypesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Export Reports</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card admin-card h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-excel fa-3x text-success mb-3"></i>
                                            <h5>Booking Report</h5>
                                            <p class="text-muted">Export all booking data to Excel</p>
                                            <button class="btn btn-outline-success">Download</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card admin-card h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                            <h5>Revenue Report</h5>
                                            <p class="text-muted">Generate PDF revenue report</p>
                                            <button class="btn btn-outline-danger">Download</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card admin-card h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-csv fa-3x text-primary mb-3"></i>
                                            <h5>Property Data</h5>
                                            <p class="text-muted">Export property details to CSV</p>
                                            <button class="btn btn-outline-primary">Download</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Settings Section -->
                <section id="settings" class="py-5 d-none">
                    <h2 class="mb-4">System Settings</h2>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">General Settings</h5>
                                </div>
                                <div class="card-body">
                                    <form id="generalSettingsForm">
                                        <div class="mb-3">
                                            <label for="systemName" class="form-label">System Name</label>
                                            <input type="text" class="form-control" id="systemName"
                                                value="TRIPSORUS Admin">
                                        </div>
                                        <div class="mb-3">
                                            <label for="timezone" class="form-label">Timezone</label>
                                            <select class="form-select" id="timezone">
                                                <option>(UTC+05:30) Chennai, Kolkata, Mumbai, New Delhi</option>
                                                <!-- More timezones would be here -->
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="dateFormat" class="form-label">Date Format</label>
                                            <select class="form-select" id="dateFormat">
                                                <option>DD/MM/YYYY</option>
                                                <option>MM/DD/YYYY</option>
                                                <option>YYYY-MM-DD</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save Settings</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Payment Settings</h5>
                                </div>
                                <div class="card-body">
                                    <form id="paymentSettingsForm">
                                        <div class="mb-3">
                                            <label class="form-label">Payment Methods</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="creditCard" checked>
                                                <label class="form-check-label" for="creditCard">Credit/Debit
                                                    Card</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="netBanking" checked>
                                                <label class="form-check-label" for="netBanking">Net Banking</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="upi">
                                                <label class="form-check-label" for="upi">UPI</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="wallet">
                                                <label class="form-check-label" for="wallet">Wallet</label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="currency" class="form-label">Default Currency</label>
                                            <select class="form-select" id="currency">
                                                <option>Indian Rupee (₹)</option>
                                                <option>US Dollar ($)</option>
                                                <option>Euro (€)</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="taxRate" class="form-label">Tax Rate (%)</label>
                                            <input type="number" class="form-control" id="taxRate" value="18">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save Settings</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">System Maintenance</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Warning:</strong> These actions may affect system availability
                            </div>
                            <div class="d-flex justify-content-between flex-wrap">
                                <button class="btn btn-outline-secondary mb-2">
                                    <i class="fas fa-database me-2"></i> Backup Database
                                </button>
                                <button class="btn btn-outline-danger mb-2">
                                    <i class="fas fa-power-off me-2"></i> System Restart
                                </button>
                                <button class="btn btn-outline-primary mb-2">
                                    <i class="fas fa-sync-alt me-2"></i> Clear Cache
                                </button>
                                <button class="btn btn-outline-info mb-2">
                                    <i class="fas fa-server me-2"></i> Server Status
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Admin Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="adminForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="adminName" class="form-label">Full Name*</label>
                                <input type="text" class="form-control" id="adminName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="adminEmail" class="form-label">Email*</label>
                                <input type="email" class="form-control" id="adminEmail" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="adminPhone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="adminPhone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="adminRole" class="form-label">Role*</label>
                                <select class="form-select" id="adminRole" required>
                                    <option value="">Select Role</option>
                                    <option value="super">Super Admin</option>
                                    <option value="property">Property Admin</option>
                                    <option value="content">Content Admin</option>
                                    <option value="support">Support Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="adminProperties" class="form-label">Assigned Properties (for Property
                                Admins)</label>
                            <select class="form-select" id="adminProperties" multiple>
                                <option>All Properties</option>
                                <option>The Grand Plaza</option>
                                <option>Riverside Inn</option>
                                <option>Green Acre Resort</option>
                                <option>Mountain View Villa</option>
                                <option>City Lights Apartment</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="row">
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="permCreate" checked>
                                        <label class="form-check-label" for="permCreate">Create</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="permEdit" checked>
                                        <label class="form-check-label" for="permEdit">Edit</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="permDelete">
                                        <label class="form-check-label" for="permDelete">Delete</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="permView" checked>
                                        <label class="form-check-label" for="permView">View</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="permExport">
                                        <label class="form-check-label" for="permExport">Export</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="permApprove">
                                        <label class="form-check-label" for="permApprove">Approve</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveAdminBtn">Create Admin</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div class="modal fade" id="editAdminModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Admin Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAdminForm">
                        <input type="hidden" id="editAdminId">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editAdminName" class="form-label">Full Name*</label>
                                <input type="text" class="form-control" id="editAdminName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editAdminEmail" class="form-label">Email*</label>
                                <input type="email" class="form-control" id="editAdminEmail" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editAdminPhone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="editAdminPhone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editAdminRole" class="form-label">Role*</label>
                                <select class="form-select" id="editAdminRole" required>
                                    <option value="">Select Role</option>
                                    <option value="super">Super Admin</option>
                                    <option value="property">Property Admin</option>
                                    <option value="content">Content Admin</option>
                                    <option value="support">Support Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editAdminProperties" class="form-label">Assigned Properties (for Property
                                Admins)</label>
                            <select class="form-select" id="editAdminProperties" multiple>
                                <option>All Properties</option>
                                <option>The Grand Plaza</option>
                                <option>Riverside Inn</option>
                                <option>Green Acre Resort</option>
                                <option>Mountain View Villa</option>
                                <option>City Lights Apartment</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="row">
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editPermCreate">
                                        <label class="form-check-label" for="editPermCreate">Create</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editPermEdit">
                                        <label class="form-check-label" for="editPermEdit">Edit</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editPermDelete">
                                        <label class="form-check-label" for="editPermDelete">Delete</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editPermView">
                                        <label class="form-check-label" for="editPermView">View</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editPermExport">
                                        <label class="form-check-label" for="editPermExport">Export</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editPermApprove">
                                        <label class="form-check-label" for="editPermApprove">Approve</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateAdminBtn">Update Admin</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Property Modal -->
    <div class="modal fade" id="addPropertyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="propertyForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="propertyName" class="form-label">Property Name*</label>
                                <input type="text" class="form-control" id="propertyName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="propertyType" class="form-label">Property Type*</label>
                                <select class="form-select" id="propertyType" required>
                                    <option value="">Select Type</option>
                                    <option value="hotel">Hotel</option>
                                    <option value="resort">Resort</option>
                                    <option value="villa">Villa</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="guesthouse">Guest House</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="propertyDescription" class="form-label">Description*</label>
                            <textarea class="form-control" id="propertyDescription" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="propertyAddress" class="form-label">Address*</label>
                                <input type="text" class="form-control" id="propertyAddress" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="propertyCity" class="form-label">City*</label>
                                <input type="text" class="form-control" id="propertyCity" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="propertyCountry" class="form-label">Country*</label>
                                <input type="text" class="form-control" id="propertyCountry" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="basePrice" class="form-label">Base Price (₹ per night)*</label>
                                <input type="number" class="form-control" id="basePrice" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="discountPrice" class="form-label">Discount Price (Optional)</label>
                                <input type="number" class="form-control" id="discountPrice">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="availableRooms" class="form-label">Available Rooms*</label>
                                <input type="number" class="form-control" id="availableRooms" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amenities</label>
                            <div class="row">
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="amenityWifi">
                                        <label class="form-check-label" for="amenityWifi">Free WiFi</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="amenityPool">
                                        <label class="form-check-label" for="amenityPool">Swimming Pool</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="amenityParking">
                                        <label class="form-check-label" for="amenityParking">Free Parking</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="amenityAC">
                                        <label class="form-check-label" for="amenityAC">Air Conditioning</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="amenityRestaurant">
                                        <label class="form-check-label" for="amenityRestaurant">Restaurant</label>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="amenityGym">
                                        <label class="form-check-label" for="amenityGym">Gym</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="propertyImages" class="form-label">Property Images*</label>
                            <input type="file" class="form-control" id="propertyImages" multiple accept="image/*"
                                required>
                            <small class="text-muted">Upload at least 3 images (max 10)</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="savePropertyBtn">Add Property</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="confirmModalBody">
                    Are you sure you want to perform this action?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmActionBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Script -->
    <script>
        // Sample data
        const adminData = [
            {
                id: 101,
                name: "Rahul Sharma",
                email: "rahul@tripsorus.com",
                phone: "+919876543210",
                role: "property",
                properties: ["The Grand Plaza", "Riverside Inn"],
                permissions: {
                    create: true,
                    edit: true,
                    delete: false,
                    view: true,
                    export: false,
                    approve: true
                },
                lastActive: "2 hours ago",
                status: "active",
                image: "https://randomuser.me/api/portraits/men/32.jpg"
            },
            {
                id: 102,
                name: "Priya Patel",
                email: "priya@tripsorus.com",
                phone: "+919876543211",
                role: "content",
                properties: [],
                permissions: {
                    create: true,
                    edit: true,
                    delete: false,
                    view: true,
                    export: true,
                    approve: false
                },
                lastActive: "Yesterday",
                status: "active",
                image: "https://randomuser.me/api/portraits/women/44.jpg"
            },
            {
                id: 103,
                name: "Arjun Singh",
                email: "arjun@tripsorus.com",
                phone: "+919876543212",
                role: "super",
                properties: ["All Properties"],
                permissions: {
                    create: true,
                    edit: true,
                    delete: true,
                    view: true,
                    export: true,
                    approve: true
                },
                lastActive: "5 minutes ago",
                status: "active",
                image: "https://randomuser.me/api/portraits/men/67.jpg"
            },
            {
                id: 104,
                name: "Neha Gupta",
                email: "neha@tripsorus.com",
                phone: "+919876543213",
                role: "property",
                properties: ["Green Acre Resort"],
                permissions: {
                    create: true,
                    edit: true,
                    delete: false,
                    view: true,
                    export: false,
                    approve: false
                },
                lastActive: "1 week ago",
                status: "inactive",
                image: "https://randomuser.me/api/portraits/women/68.jpg"
            }
        ];

        const propertyData = [
            {
                id: "PR1001",
                name: "Grand Plaza Hotel",
                type: "Hotel",
                location: "Kolkata, India",
                price: "₹5,200",
                status: "active",
                admin: "Rahul Sharma",
                image: "https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80",
                added: "2 days ago",
                rating: "5-star"
            },
            {
                id: "PR1002",
                name: "Beach View Resort",
                type: "Resort",
                location: "Goa, India",
                price: "₹7,500",
                status: "active",
                admin: "Neha Gupta",
                image: "https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80",
                added: "1 week ago",
                rating: "4-star"
            },
            {
                id: "PR1003",
                name: "Mountain Villa",
                type: "Villa",
                location: "Darjeeling, India",
                price: "₹12,000",
                status: "active",
                admin: "Arjun Singh",
                image: "https://images.unsplash.com/photo-1580587771525-78b9dba3b914?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80",
                added: "2 weeks ago",
                rating: "Luxury"
            },
            {
                id: "PR1004",
                name: "City Apartment",
                type: "Apartment",
                location: "Mumbai, India",
                price: "₹4,500",
                status: "pending",
                admin: "Priya Patel",
                image: "https://images.unsplash.com/photo-1493809842364-78817add7ffb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80",
                added: "3 weeks ago",
                rating: "Premium"
            },
            {
                id: "PR1005",
                name: "Luxury Suite",
                type: "Hotel",
                location: "Delhi, India",
                price: "₹6,500",
                status: "active",
                admin: "Rahul Sharma",
                image: "https://images.unsplash.com/photo-1512917774080-9991f1c4c750?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80",
                added: "1 month ago",
                rating: "5-star"
            }
        ];

        document.addEventListener('DOMContentLoaded', function () {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('sidebar');

            mobileMenuBtn.addEventListener('click', function () {
                sidebar.classList.toggle('active');
            });

            // Navigation handling
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    const target = this.getAttribute('href');
                    if (target.startsWith('#')) {
                        e.preventDefault();

                        // Hide all sections
                        document.querySelectorAll('main section').forEach(section => {
                            section.classList.add('d-none');
                        });

                        // Show target section
                        document.querySelector(target).classList.remove('d-none');

                        // Update active link
                        navLinks.forEach(navLink => navLink.classList.remove('active'));
                        this.classList.add('active');

                        // Close mobile menu if open
                        if (window.innerWidth <= 768) {
                            sidebar.classList.remove('active');
                        }
                    }
                });
            });

            // Load admin data
            loadAdminData();

            // Load property data
            loadPropertyData();

            // Load recent properties
            loadRecentProperties();

            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Revenue (₹)',
                        data: [125000, 189000, 142000, 178000, 156000, 210000, 185000, 230000, 205000, 245000, 198000, 284760],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return '₹' + context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Booking Trends Chart
            const bookingTrendsCtx = document.getElementById('bookingTrendsChart').getContext('2d');
            const bookingTrendsChart = new Chart(bookingTrendsCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Bookings',
                        data: [45, 78, 56, 89, 76, 112],
                        backgroundColor: 'rgba(75, 192, 192, 0.7)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });

            // Property Types Chart
            const propertyTypesCtx = document.getElementById('propertyTypesChart').getContext('2d');
            const propertyTypesChart = new Chart(propertyTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Hotels', 'Resorts', 'Villas', 'Apartments', 'Guest Houses'],
                    datasets: [{
                        data: [65, 32, 18, 24, 8],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });

            // Save admin form
            document.getElementById('saveAdminBtn').addEventListener('click', function () {
                const name = document.getElementById('adminName').value;
                const email = document.getElementById('adminEmail').value;
                const phone = document.getElementById('adminPhone').value;
                const role = document.getElementById('adminRole').value;

                if (!name || !email || !role) {
                    alert('Please fill all required fields');
                    return;
                }

                // Show loading spinner
                document.getElementById('loadingSpinner').style.display = 'flex';

                // Simulate API call
                setTimeout(() => {
                    // Create new admin object
                    const newAdmin = {
                        id: Math.floor(Math.random() * 900) + 100,
                        name: name,
                        email: email,
                        phone: phone,
                        role: role,
                        properties: Array.from(document.getElementById('adminProperties').selectedOptions).map(opt => opt.value),
                        permissions: {
                            create: document.getElementById('permCreate').checked,
                            edit: document.getElementById('permEdit').checked,
                            delete: document.getElementById('permDelete').checked,
                            view: document.getElementById('permView').checked,
                            export: document.getElementById('permExport').checked,
                            approve: document.getElementById('permApprove').checked
                        },
                        lastActive: "Just now",
                        status: "active",
                        image: `https://randomuser.me/api/portraits/${Math.random() > 0.5 ? 'men' : 'women'}/${Math.floor(Math.random() * 100)}.jpg`
                    };

                    // Add to admin data
                    adminData.push(newAdmin);

                    // Reload admin table
                    loadAdminData();

                    // Hide modal
                    bootstrap.Modal.getInstance(document.getElementById('addAdminModal')).hide();

                    // Reset form
                    document.getElementById('adminForm').reset();

                    // Hide loading spinner
                    document.getElementById('loadingSpinner').style.display = 'none';

                    // Show success message
                    alert('Admin created successfully!');
                }, 1500);
            });

            // Edit admin button click handler
            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('edit-admin') || e.target.parentElement.classList.contains('edit-admin')) {
                    const adminId = e.target.getAttribute('data-id') || e.target.parentElement.getAttribute('data-id');
                    const admin = adminData.find(a => a.id == adminId);

                    if (admin) {
                        // Fill edit form
                        document.getElementById('editAdminId').value = admin.id;
                        document.getElementById('editAdminName').value = admin.name;
                        document.getElementById('editAdminEmail').value = admin.email;
                        document.getElementById('editAdminPhone').value = admin.phone;
                        document.getElementById('editAdminRole').value = admin.role;

                        // Select properties
                        const propertySelect = document.getElementById('editAdminProperties');
                        Array.from(propertySelect.options).forEach(option => {
                            option.selected = admin.properties.includes(option.value);
                        });

                        // Set permissions
                        document.getElementById('editPermCreate').checked = admin.permissions.create;
                        document.getElementById('editPermEdit').checked = admin.permissions.edit;
                        document.getElementById('editPermDelete').checked = admin.permissions.delete;
                        document.getElementById('editPermView').checked = admin.permissions.view;
                        document.getElementById('editPermExport').checked = admin.permissions.export;
                        document.getElementById('editPermApprove').checked = admin.permissions.approve;

                        // Show modal
                        const editModal = new bootstrap.Modal(document.getElementById('editAdminModal'));
                        editModal.show();
                    }
                }
            });

            // Update admin button click handler
            document.getElementById('updateAdminBtn').addEventListener('click', function () {
                const adminId = document.getElementById('editAdminId').value;
                const adminIndex = adminData.findIndex(a => a.id == adminId);

                if (adminIndex !== -1) {
                    // Show loading spinner
                    document.getElementById('loadingSpinner').style.display = 'flex';

                    // Simulate API call
                    setTimeout(() => {
                        // Update admin data
                        adminData[adminIndex].name = document.getElementById('editAdminName').value;
                        adminData[adminIndex].email = document.getElementById('editAdminEmail').value;
                        adminData[adminIndex].phone = document.getElementById('editAdminPhone').value;
                        adminData[adminIndex].role = document.getElementById('editAdminRole').value;
                        adminData[adminIndex].properties = Array.from(document.getElementById('editAdminProperties').selectedOptions).map(opt => opt.value);
                        adminData[adminIndex].permissions = {
                            create: document.getElementById('editPermCreate').checked,
                            edit: document.getElementById('editPermEdit').checked,
                            delete: document.getElementById('editPermDelete').checked,
                            view: document.getElementById('editPermView').checked,
                            export: document.getElementById('editPermExport').checked,
                            approve: document.getElementById('editPermApprove').checked
                        };

                        // Reload admin table
                        loadAdminData();

                        // Hide modal
                        bootstrap.Modal.getInstance(document.getElementById('editAdminModal')).hide();

                        // Hide loading spinner
                        document.getElementById('loadingSpinner').style.display = 'none';

                        // Show success message
                        alert('Admin updated successfully!');
                    }, 1500);
                }
            });

            // Revoke/Activate admin button click handler
            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('revoke-admin') || e.target.parentElement.classList.contains('revoke-admin') ||
                    e.target.classList.contains('activate-admin') || e.target.parentElement.classList.contains('activate-admin')) {
                    const adminId = e.target.getAttribute('data-id') || e.target.parentElement.getAttribute('data-id');
                    const admin = adminData.find(a => a.id == adminId);

                    if (admin) {
                        const action = e.target.classList.contains('revoke-admin') || e.target.parentElement.classList.contains('revoke-admin') ? 'revoke' : 'activate';
                        const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

                        // Set modal content based on action
                        if (action === 'revoke') {
                            document.getElementById('confirmModalTitle').textContent = 'Revoke Admin Access';
                            document.getElementById('confirmModalBody').textContent = `Are you sure you want to revoke access for ${admin.name}?`;
                        } else {
                            document.getElementById('confirmModalTitle').textContent = 'Activate Admin Account';
                            document.getElementById('confirmModalBody').textContent = `Are you sure you want to activate ${admin.name}'s account?`;
                        }

                        // Show modal
                        confirmModal.show();

                        // Handle confirm action
                        document.getElementById('confirmActionBtn').onclick = function () {
                            // Show loading spinner
                            document.getElementById('loadingSpinner').style.display = 'flex';

                            // Simulate API call
                            setTimeout(() => {
                                // Update admin status
                                admin.status = action === 'revoke' ? 'inactive' : 'active';

                                // Reload admin table
                                loadAdminData();

                                // Hide modal
                                confirmModal.hide();

                                // Hide loading spinner
                                document.getElementById('loadingSpinner').style.display = 'none';

                                // Show success message
                                alert(`Admin ${action === 'revoke' ? 'revoked' : 'activated'} successfully!`);
                            }, 1000);
                        };
                    }
                }
            });

            // Save property form
            document.getElementById('savePropertyBtn').addEventListener('click', function () {
                const name = document.getElementById('propertyName').value;
                const type = document.getElementById('propertyType').value;
                const description = document.getElementById('propertyDescription').value;
                const address = document.getElementById('propertyAddress').value;
                const city = document.getElementById('propertyCity').value;
                const country = document.getElementById('propertyCountry').value;
                const basePrice = document.getElementById('basePrice').value;
                const discountPrice = document.getElementById('discountPrice').value;
                const availableRooms = document.getElementById('availableRooms').value;

                if (!name || !type || !description || !address || !city || !country || !basePrice || !availableRooms) {
                    alert('Please fill all required fields');
                    return;
                }

                // Show loading spinner
                document.getElementById('loadingSpinner').style.display = 'flex';

                // Simulate API call
                setTimeout(() => {
                    // Create new property object
                    const newProperty = {
                        id: "PR" + (Math.floor(Math.random() * 9000) + 1000),
                        name: name,
                        type: type,
                        location: `${city}, ${country}`,
                        price: `₹${basePrice}`,
                        status: "active",
                        admin: "You",
                        image: "https://images.unsplash.com/photo-1582719471386-c1a3927a3a8b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80",
                        added: "Just now",
                        rating: "New"
                    };

                    // Add to property data
                    propertyData.unshift(newProperty);

                    // Reload property tables
                    loadPropertyData();
                    loadRecentProperties();

                    // Hide modal
                    bootstrap.Modal.getInstance(document.getElementById('addPropertyModal')).hide();

                    // Reset form
                    document.getElementById('propertyForm').reset();

                    // Hide loading spinner
                    document.getElementById('loadingSpinner').style.display = 'none';

                    // Show success message
                    alert('Property added successfully!');
                }, 1500);
            });

            // Search property button click handler
            document.getElementById('searchPropertyBtn').addEventListener('click', function () {
                const searchTerm = document.getElementById('propertySearch').value.toLowerCase();
                if (searchTerm) {
                    const filteredProperties = propertyData.filter(property =>
                        property.name.toLowerCase().includes(searchTerm) ||
                        property.type.toLowerCase().includes(searchTerm) ||
                        property.location.toLowerCase().includes(searchTerm)
                    );
                    loadPropertyData(filteredProperties);
                } else {
                    loadPropertyData();
                }
            });

            // General settings form submission
            document.getElementById('generalSettingsForm').addEventListener('submit', function (e) {
                e.preventDefault();
                alert('General settings saved successfully!');
            });

            // Payment settings form submission
            document.getElementById('paymentSettingsForm').addEventListener('submit', function (e) {
                e.preventDefault();
                alert('Payment settings saved successfully!');
            });
        });

        // Function to load admin data
        function loadAdminData() {
            const adminTableBody = document.getElementById('adminTableBody');
            adminTableBody.innerHTML = '';

            adminData.forEach(admin => {
                const row = document.createElement('tr');

                // Determine badge class based on role
                let badgeClass = 'bg-secondary';
                if (admin.role === 'super') badgeClass = 'bg-danger';
                if (admin.role === 'property') badgeClass = 'badge-admin';
                if (admin.role === 'content') badgeClass = 'bg-info';
                if (admin.role === 'support') badgeClass = 'bg-primary';

                // Determine status badge
                const statusBadge = admin.status === 'active' ?
                    '<span class="badge bg-success">Active</span>' :
                    '<span class="badge bg-warning text-dark">Inactive</span>';

                // Determine action buttons
                let actionButtons = '';
                if (admin.role === 'super') {
                    actionButtons = `
                        <button class="btn btn-sm btn-outline-primary me-2 edit-admin" data-id="${admin.id}" data-bs-toggle="tooltip" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" disabled data-bs-toggle="tooltip" title="Cannot revoke super admin">
                            <i class="fas fa-user-slash"></i>
                        </button>
                    `;
                } else if (admin.status === 'active') {
                    actionButtons = `
                        <button class="btn btn-sm btn-outline-primary me-2 edit-admin" data-id="${admin.id}" data-bs-toggle="tooltip" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger revoke-admin" data-id="${admin.id}" data-bs-toggle="tooltip" title="Revoke Access">
                            <i class="fas fa-user-slash"></i>
                        </button>
                    `;
                } else {
                    actionButtons = `
                        <button class="btn btn-sm btn-outline-primary me-2 edit-admin" data-id="${admin.id}" data-bs-toggle="tooltip" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success activate-admin" data-id="${admin.id}" data-bs-toggle="tooltip" title="Activate">
                            <i class="fas fa-user-check"></i>
                        </button>
                    `;
                }

                row.innerHTML = `
                    <td>${admin.id}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="${admin.image}" class="rounded-circle me-2" width="30" height="30" alt="${admin.name}">
                            <span>${admin.name}</span>
                        </div>
                    </td>
                    <td>${admin.email}</td>
                    <td><span class="badge ${badgeClass}">${admin.role.charAt(0).toUpperCase() + admin.role.slice(1)} Admin</span></td>
                    <td>${admin.lastActive}</td>
                    <td>${statusBadge}</td>
                    <td class="admin-actions">
                        ${actionButtons}
                    </td>
                `;

                adminTableBody.appendChild(row);
            });

            // Reinitialize tooltips for new elements
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Function to load property data
        function loadPropertyData(properties = propertyData) {
            const propertyTableBody = document.getElementById('propertyTableBody');
            propertyTableBody.innerHTML = '';

            properties.forEach(property => {
                const row = document.createElement('tr');

                // Determine status badge
                let statusBadge = '';
                if (property.status === 'active') {
                    statusBadge = '<span class="badge badge-active">Active</span>';
                } else if (property.status === 'pending') {
                    statusBadge = '<span class="badge badge-pending">Pending</span>';
                } else {
                    statusBadge = '<span class="badge badge-inactive">Inactive</span>';
                }

                row.innerHTML = `
                    <td>${property.id}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="${property.image}" class="rounded me-2" width="40" height="40" alt="${property.name}">
                            <div>
                                <h6 class="mb-0">${property.name}</h6>
                                <small class="text-muted">${property.rating}</small>
                            </div>
                        </div>
                    </td>
                    <td>${property.type}</td>
                    <td>${property.location}</td>
                    <td>${property.price}</td>
                    <td>${statusBadge}</td>
                    <td>${property.admin}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="tooltip" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success me-1" data-bs-toggle="tooltip" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;

                propertyTableBody.appendChild(row);
            });

            // Reinitialize tooltips for new elements
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Function to load recent properties
        function loadRecentProperties() {
            const recentPropertiesContainer = document.getElementById('recentProperties');
            recentPropertiesContainer.innerHTML = '';

            // Get first 3 properties
            const recentProperties = propertyData.slice(0, 3);

            recentProperties.forEach(property => {
                const col = document.createElement('div');
                col.className = 'col-sm-6 col-md-4 mb-4';

                // Determine status badge
                let statusBadge = '';
                if (property.status === 'active') {
                    statusBadge = '<span class="badge badge-active">Active</span>';
                } else if (property.status === 'pending') {
                    statusBadge = '<span class="badge badge-pending">Pending</span>';
                } else {
                    statusBadge = '<span class="badge badge-inactive">Inactive</span>';
                }

                col.innerHTML = `
                    <div class="property-card">
                        <img src="${property.image}" class="property-img w-100" alt="${property.name}">
                        <div class="p-3">
                            <h5>${property.name}</h5>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary">${property.type}</span>
                                <span class="text-success fw-bold">${property.price}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                ${statusBadge}
                                <small class="text-muted">${property.rating}</small>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> ${property.location}</small>
                                <small class="text-muted">Added: ${property.added}</small>
                            </div>
                        </div>
                    </div>
                `;

                recentPropertiesContainer.appendChild(col);
            });
        }
    </script>
</body>

</html>