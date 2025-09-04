<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Properties - TRIPSORUS Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="styles/style.css">
  <link rel="icon" href="../images/favicon.ico" type="image/ico" />
</head>
<body>
  <!-- Sidebar Navigation -->
   <?php include 'sidebar.php'; ?>
  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid">
      <!-- Page Header -->
      <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h2 class="mb-0">Manage Properties</h2>
            <p class="text-muted mb-0">View and manage all your properties</p>
          </div>
          <a href="add-property.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Add Property
          </a>
        </div>
      </div>

      <!-- Property Filters -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Filter Properties</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3 mb-3">
              <label for="propertyTypeFilter" class="form-label">Property Type</label>
              <select class="form-select" id="propertyTypeFilter">
                <option value="">All Types</option>
                <option value="hotel">Hotel</option>
                <option value="resort">Resort</option>
                <option value="villa">Villa</option>
                <option value="apartment">Apartment</option>
                <option value="guesthouse">Guesthouse</option>
              </select>
            </div>
            <div class="col-md-3 mb-3">
              <label for="statusFilter" class="form-label">Status</label>
              <select class="form-select" id="statusFilter">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="draft">Draft</option>
              </select>
            </div>
            <div class="col-md-3 mb-3">
              <label for="cityFilter" class="form-label">City</label>
              <select class="form-select" id="cityFilter">
                <option value="">All Cities</option>
                <!-- This will be populated dynamically from the database -->
              </select>
            </div>
            <div class="col-md-3 mb-3 d-flex align-items-end">
              <button id="applyFiltersBtn" class="btn btn-primary w-100">
                <i class="fas fa-filter me-2"></i> Apply Filters
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Properties Table -->
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">All Properties</h5>
          <div class="input-group" style="width: 300px;">
            <input type="text" id="searchInput" class="form-control search-box" placeholder="Search properties...">
            <button id="searchBtn" class="btn btn-outline-secondary search-btn" type="button">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
        
        <!-- Loader -->
        <div id="loader" class="loader">
          <div class="spinner-border text-primary loader-spinner" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3">Loading properties...</p>
        </div>
        
        <!-- Empty State -->
        <div id="emptyState" class="empty-state">
          <i class="fas fa-building"></i>
          <h4>No Properties Found</h4>
          <p class="text-muted">Try adjusting your search or filter criteria</p>
          <button id="resetFiltersBtn" class="btn btn-primary mt-2">
            <i class="fas fa-sync me-2"></i> Reset Filters
          </button>
        </div>
        
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Property</th>
                  <th>Type</th>
                  <th>Location</th>
                  <th>Price (₹)</th>
                  <th>Rooms</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="propertiesTableBody">
                <!-- Properties will be loaded here dynamically -->
              </tbody>
            </table>
          </div>
        </div>
        
        <!-- Pagination -->
        <div class="card-footer">
          <nav aria-label="Page navigation">
            <ul id="pagination" class="pagination justify-content-center mb-0">
              <!-- Pagination will be loaded here dynamically -->
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Global variables
    let currentPage = 1;
    const propertiesPerPage = 5;
    
    // DOM Elements
    const tableBody = document.getElementById('propertiesTableBody');
    const pagination = document.getElementById('pagination');
    const loader = document.getElementById('loader');
    const emptyState = document.getElementById('emptyState');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const applyFiltersBtn = document.getElementById('applyFiltersBtn');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    const cityFilter = document.getElementById('cityFilter');
    
    // Initialize the page
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
      
      // Load cities for filter dropdown
      loadCities();
      
      // Load properties
      loadProperties();
      
      // Event listeners
      searchBtn.addEventListener('click', function() {
        currentPage = 1;
        loadProperties();
      });
      
      searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
          currentPage = 1;
          loadProperties();
        }
      });
      
      applyFiltersBtn.addEventListener('click', function() {
        currentPage = 1;
        loadProperties();
      });
      
      resetFiltersBtn.addEventListener('click', function() {
        document.getElementById('propertyTypeFilter').value = '';
        document.getElementById('statusFilter').value = '';
        cityFilter.value = '';
        searchInput.value = '';
        currentPage = 1;
        loadProperties();
      });
    });
    
    // Load cities from backend for filter dropdown
    function loadCities() {
      fetch('api/get_cities.php')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Clear existing options except the first one
            while (cityFilter.options.length > 1) {
              cityFilter.remove(1);
            }
            
            // Add new city options
            data.cities.forEach(city => {
              const option = document.createElement('option');
              option.value = city;
              option.textContent = city;
              cityFilter.appendChild(option);
            });
          }
        })
        .catch(error => {
          console.error('Error loading cities:', error);
        });
    }
    
    // Load properties from backend
    function loadProperties() {
      loader.style.display = 'block';
      tableBody.innerHTML = '';
      emptyState.style.display = 'none';
      
      // Get filter values
      const searchTerm = searchInput.value.trim();
      const typeFilter = document.getElementById('propertyTypeFilter').value;
      const statusFilter = document.getElementById('statusFilter').value;
      const cityFilterValue = cityFilter.value;
      
      // Build query parameters
      const params = new URLSearchParams();
      if (searchTerm) params.append('search', searchTerm);
      if (typeFilter) params.append('type', typeFilter);
      if (statusFilter) params.append('status', statusFilter);
      if (cityFilterValue) params.append('city', cityFilterValue);
      params.append('page', currentPage);
      params.append('per_page', propertiesPerPage);
      
      fetch(`api/get_properties.php?${params.toString()}`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            renderProperties(data.data, data.pagination);
          } else {
            showError(data.message || 'Failed to load properties');
          }
        })
        .catch(error => {
          console.error('Error loading properties:', error);
          showError('Failed to load properties. Please try again.');
        })
        .finally(() => {
          loader.style.display = 'none';
        });
    }
    
    // Render properties to the table
    function renderProperties(properties, paginationData) {
      // Clear table body
      tableBody.innerHTML = '';
      
      // Show empty state if no properties
      if (!properties || properties.length === 0) {
        emptyState.style.display = 'block';
        renderPagination(paginationData);
        return;
      }
      
      // Hide empty state
      emptyState.style.display = 'none';
      
      // Render properties
      properties.forEach(property => {
        const row = document.createElement('tr');
        
        // Get type class
        let typeClass = '';
        switch(property.property_type) {
          case 'hotel': typeClass = 'type-hotel'; break;
          case 'resort': typeClass = 'type-resort'; break;
          case 'villa': typeClass = 'type-villa'; break;
          case 'apartment': typeClass = 'type-apartment'; break;
          case 'guesthouse': typeClass = 'type-guesthouse'; break;
        }
        
        // Get status badge
        let statusBadge = '';
        switch(property.status) {
          case 'active':
            statusBadge = '<span class="badge status-badge active">Active</span>';
            break;
          case 'inactive':
            statusBadge = '<span class="badge status-badge inactive">Inactive</span>';
            break;
          case 'draft':
            statusBadge = '<span class="badge status-badge draft">Draft</span>';
            break;
        }
        
        // Set default image if none exists
        const propertyImage = property.main_image || 'https://images.unsplash.com/photo-1566073771259-6a8506099945?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=50&w=50&ixlib=rb-1.2.1&q=80';
        
        row.innerHTML = `
          <td>${property.property_id}</td>
          <td>
            <div class="d-flex align-items-center">
              <img src="${propertyImage}" class="property-img me-3" alt="${property.property_name}">
              <div>
                <h6 class="mb-0">${property.property_name}</h6>
                <small class="text-muted">${property.description}</small>
              </div>
            </div>
          </td>
          <td><span class="property-type ${typeClass}">${property.property_type.charAt(0).toUpperCase() + property.property_type.slice(1)}</span></td>
          <td>${property.location}</td>
          <td>${property.min_price ? '₹' + property.min_price.toLocaleString() : 'N/A'}</td>
          <td>${property.room_count || 0}</td>
          <td>${statusBadge}</td>
          <td>
            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="tooltip" title="Edit" onclick="editProperty(${property.id})">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-success me-1" data-bs-toggle="tooltip" title="View" onclick="viewProperty(${property.id})">
              <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Delete" onclick="deleteProperty(${property.id})">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        `;
        
        tableBody.appendChild(row);
      });
      
      // Render pagination
      renderPagination(paginationData);
      
      // Reinitialize tooltips for new elements
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.forEach(tooltipTriggerEl => {
        new bootstrap.Tooltip(tooltipTriggerEl);
      });
    }
    
    // Render pagination controls
    function renderPagination(paginationData) {
      pagination.innerHTML = '';
      
      if (!paginationData || paginationData.last_page <= 1) return;
      
      // Previous button
      const prevLi = document.createElement('li');
      prevLi.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
      prevLi.innerHTML = `
        <a class="page-link" href="#" tabindex="-1" aria-disabled="${currentPage === 1}" id="prevPage">
          <i class="fas fa-chevron-left"></i>
        </a>
      `;
      pagination.appendChild(prevLi);
      
      // Page numbers
      for (let i = 1; i <= paginationData.last_page; i++) {
        const pageLi = document.createElement('li');
        pageLi.className = 'page-item' + (i === currentPage ? ' active' : '');
        pageLi.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
        pagination.appendChild(pageLi);
      }
      
      // Next button
      const nextLi = document.createElement('li');
      nextLi.className = 'page-item' + (currentPage === paginationData.last_page ? ' disabled' : '');
      nextLi.innerHTML = `
        <a class="page-link" href="#" id="nextPage">
          <i class="fas fa-chevron-right"></i>
        </a>
      `;
      pagination.appendChild(nextLi);
      
      // Add event listeners
      document.getElementById('prevPage')?.addEventListener('click', (e) => {
        e.preventDefault();
        if (currentPage > 1) {
          currentPage--;
          loadProperties();
        }
      });
      
      document.getElementById('nextPage')?.addEventListener('click', (e) => {
        e.preventDefault();
        if (currentPage < paginationData.last_page) {
          currentPage++;
          loadProperties();
        }
      });
      
      const pageLinks = document.querySelectorAll('.page-link[data-page]');
      pageLinks.forEach(link => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          currentPage = parseInt(link.getAttribute('data-page'));
          loadProperties();
        });
      });
    }
    
    // Show error message
    function showError(message) {
      emptyState.innerHTML = `
        <i class="fas fa-exclamation-triangle text-danger"></i>
        <h4>Error Loading Properties</h4>
        <p class="text-muted">${message}</p>
        <button id="retryBtn" class="btn btn-primary mt-2">
          <i class="fas fa-sync me-2"></i> Retry
        </button>
      `;
      emptyState.style.display = 'block';
      
      document.getElementById('retryBtn')?.addEventListener('click', loadProperties);
    }
    
    // Property action functions
    function editProperty(id) {
      window.location.href = `edit_property.php?id=${id}`;
    }
    
    function viewProperty(id) {
      window.location.href = `view_property.php?id=${id}`;
    }
    
    function deleteProperty(id) {
      if (confirm('Are you sure you want to delete this property?')) {
        fetch(`api/delete_property.php?id=${id}`, {
          method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Property deleted successfully');
            loadProperties();
          } else {
            alert('Error deleting property: ' + (data.message || 'Unknown error'));
          }
        })
        .catch(error => {
          console.error('Error deleting property:', error);
          alert('Error deleting property. Please try again.');
        });
      }
    }
  </script>
</body>
</html>