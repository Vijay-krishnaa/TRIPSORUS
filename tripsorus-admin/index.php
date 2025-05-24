<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - TRIPSORUS Admin</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles/style.css">
</head>

<body>
  <!-- Sidebar Navigation -->
  <div class="sidebar p-3">
    <h4 class="text-center mb-4">TRIPSORUS Admin</h4>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link" href="index.html">
          <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="add-property.html">
          <i class="fas fa-plus-circle"></i> <span>Add Property</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="properties.html">
          <i class="fas fa-hotel"></i> <span>Manage Properties</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="bookings.html">
          <i class="fas fa-calendar-check"></i> <span>Bookings</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">
          <i class="fas fa-cog"></i> <span>Settings</span>
        </a>
      </li>
      <li class="nav-item mt-3">
        <a class="nav-link text-danger" href="#">
          <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </a>
      </li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dashboard Overview</h2>
        <div class="d-flex">
          <div class="input-group me-3" style="width: 250px;">
            <input type="text" class="form-control" placeholder="Search...">
            <button class="btn btn-outline-secondary" type="button">
              <i class="fas fa-search"></i>
            </button>
          </div>
          <button class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Add Property
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="row">
        <div class="col-md-3">
          <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-hotel"></i>
            <div class="count">42</div>
            <div class="title">Total Properties</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card" style="background: linear-gradient(135deg, #ff758c 0%, #ff7eb3 100%);">
            <i class="fas fa-calendar-check"></i>
            <div class="count">128</div>
            <div class="title">Total Bookings</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <i class="fas fa-users"></i>
            <div class="count">56</div>
            <div class="title">Active Guests</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <i class="fas fa-rupee-sign"></i>
            <div class="count">₹2,84,760</div>
            <div class="title">This Month Revenue</div>
          </div>
        </div>
      </div>

      <!-- Recent Bookings -->
      <div class="row mt-4">
        <div class="col-md-8">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Recent Bookings</h5>
              <a href="bookings.html" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Booking ID</th>
                      <th>Property</th>
                      <th>Guest</th>
                      <th>Dates</th>
                      <th>Amount</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>#TRP-78945</td>
                      <td>Grand Plaza Hotel</td>
                      <td>Rahul Sharma</td>
                      <td>15-20 Jun 2023</td>
                      <td>₹12,500</td>
                      <td><span class="badge bg-success">Confirmed</span></td>
                      <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                          <i class="fas fa-eye"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>#TRP-78944</td>
                      <td>Beach View Resort</td>
                      <td>Priya Patel</td>
                      <td>10-15 Jun 2023</td>
                      <td>₹18,200</td>
                      <td><span class="badge bg-success">Confirmed</span></td>
                      <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                          <i class="fas fa-eye"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>#TRP-78943</td>
                      <td>Mountain Villa</td>
                      <td>Arjun Singh</td>
                      <td>05-12 Jun 2023</td>
                      <td>₹25,000</td>
                      <td><span class="badge bg-warning text-dark">Pending</span></td>
                      <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                          <i class="fas fa-eye"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>#TRP-78942</td>
                      <td>City Apartment</td>
                      <td>Neha Gupta</td>
                      <td>01-05 Jun 2023</td>
                      <td>₹8,500</td>
                      <td><span class="badge bg-danger">Cancelled</span></td>
                      <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                          <i class="fas fa-eye"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>#TRP-78941</td>
                      <td>Luxury Suite</td>
                      <td>Vikram Joshi</td>
                      <td>28 May - 02 Jun 2023</td>
                      <td>₹15,750</td>
                      <td><span class="badge bg-success">Confirmed</span></td>
                      <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                          <i class="fas fa-eye"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Properties -->
        <div class="col-md-4">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Recent Properties</h5>
              <a href="properties.html" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
              <div class="list-group">
                <a href="#" class="list-group-item list-group-item-action">
                  <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">Grand Plaza Hotel</h6>
                    <small class="text-success">Active</small>
                  </div>
                  <p class="mb-1">Kolkata, India</p>
                  <small>Added: 2 days ago</small>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                  <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">Beach View Resort</h6>
                    <small class="text-success">Active</small>
                  </div>
                  <p class="mb-1">Goa, India</p>
                  <small>Added: 1 week ago</small>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                  <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">Mountain Villa</h6>
                    <small class="text-success">Active</small>
                  </div>
                  <p class="mb-1">Darjeeling, India</p>
                  <small>Added: 2 weeks ago</small>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                  <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">City Apartment</h6>
                    <small class="text-warning">Draft</small>
                  </div>
                  <p class="mb-1">Mumbai, India</p>
                  <small>Added: 3 weeks ago</small>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                  <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">Luxury Suite</h6>
                    <small class="text-success">Active</small>
                  </div>
                  <p class="mb-1">Delhi, India</p>
                  <small>Added: 1 month ago</small>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Revenue Chart -->
      <div class="row mt-4">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0">Monthly Revenue</h5>
            </div>
            <div class="card-body">
              <canvas id="revenueChart" height="100"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Custom Script -->
  <script src="scripts/script.js"></script>

  <script>
    // Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
          label: 'Revenue (₹)',
          data: [125000, 189000, 142000, 178000, 156000, 210000, 185000, 230000, 205000, 245000, 198000, 284760],
          backgroundColor: 'rgba(54, 162, 235, 0.7)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
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
  </script>
</body>

</html>