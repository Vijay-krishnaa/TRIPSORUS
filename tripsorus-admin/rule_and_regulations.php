<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Rules Management - Admin Panel</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  body {
    background-color: #f8fafc;
    color: #333;
    line-height: 1.6;
  }

  .container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
  }

  header {
    background: linear-gradient(135deg, #1a4b8c, #2b6cb0);
    color: white;
    padding: 20px 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    text-align: center;
    margin-bottom: 30px;
  }

  .logo {
    font-size: 28px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .logo i {
    margin-right: 10px;
  }

  .card {
    background: white;
    border-radius: 10px;
    padding: 30px;
    margin-bottom: 25px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
  }

  h1 {
    font-size: 32px;
    margin-bottom: 20px;
    color: #2c5282;
    text-align: center;
  }

  h2 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #2b6cb0;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
  }

  h3 {
    font-size: 20px;
    margin: 25px 0 15px;
    color: #4a5568;
  }

  .admin-panel {
    background: #f8fafc;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    margin-bottom: 25px;
  }

  .form-group {
    margin-bottom: 25px;
  }

  label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: #4a5568;
    font-size: 18px;
  }

  input,
  textarea,
  select {
    width: 100%;
    padding: 14px;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    font-size: 16px;
    background: white;
  }

  textarea {
    min-height: 120px;
    resize: vertical;
  }

  button {
    background: #2b6cb0;
    color: white;
    border: none;
    padding: 14px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s;
    margin-right: 15px;
    margin-bottom: 15px;
    display: inline-flex;
    align-items: center;
  }

  button i {
    margin-right: 8px;
  }

  button:hover {
    background: #1a4b8c;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  button.delete {
    background: #e53e3e;
  }

  button.delete:hover {
    background: #c53030;
  }

  .notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    background: #38a169;
    color: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: none;
    z-index: 1000;
  }

  .error-notification {
    background: #e53e3e;
  }

  .rules-list {
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    background: #f8fafc;
  }

  .rule-item {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
    background: white;
    margin-bottom: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  }

  .rule-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
  }

  .rule-content {
    color: #4a5568;
    margin-bottom: 15px;
    line-height: 1.6;
  }

  .rule-category {
    display: inline-block;
    background: #ebf8ff;
    color: #2b6cb0;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 12px;
  }

  .user-link {
    display: block;
    text-align: center;
    margin-top: 25px;
    color: #2b6cb0;
    text-decoration: none;
    font-weight: 600;
    font-size: 18px;
  }

  .user-link:hover {
    text-decoration: underline;
  }

  .content-inputs {
    display: grid;
    grid-template-columns: 1fr;
    gap: 15px;
    margin-bottom: 20px;
  }

  .content-input {
    display: flex;
    align-items: center;
    background: white;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
  }

  .content-input input {
    flex: 1;
    border: none;
    padding: 10px;
    margin-right: 10px;
  }

  .remove-content {
    background: #e53e3e;
    color: white;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
  }

  .add-content-btn {
    background: #38a169;
    margin-bottom: 20px;
  }

  .add-content-btn:hover {
    background: #2f855a;
  }

  .section-title {
    font-size: 22px;
    color: #2b6cb0;
    margin: 25px 0 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
  }

  .loading {
    text-align: center;
    padding: 20px;
    color: #4a5568;
  }

  @media (max-width: 768px) {
    .container {
      padding: 15px;
    }

    .card {
      padding: 20px;
    }

    h1 {
      font-size: 28px;
    }

    h2 {
      font-size: 22px;
    }

    button {
      width: 100%;
      margin-right: 0;
    }
  }
  </style>
</head>

<body>
  <header>
    <div class="logo">
      <i class="fas fa-hotel"></i>
      Grand Plaza Hotel - Rules Management
    </div>
  </header>

  <div class="container">
    <div class="card">
      <h1>Rules Management</h1>
      <p>As an administrator, you can update the hotel rules and regulations here. Changes will be reflected on the user
        view.</p>

      <div class="admin-panel">
        <h2>Add New Rule</h2>

        <div class="form-group">
          <div class="section-title">Category</div>
          <select id="rule-category">
            <option value="">Loading categories...</option>
          </select>
        </div>

        <div class="form-group">
          <div class="section-title">Rule Title</div>
          <input type="text" id="rule-title" placeholder="Enter rule title">
        </div>

        <div class="form-group">
          <div class="section-title">Rule Content Items</div>
          <div id="content-inputs-container">
            <div class="content-inputs">
              <div class="content-input">
                <input type="text" class="rule-content-item" placeholder="Content item 1">
                <button class="remove-content">&times;</button>
              </div>
              <div class="content-input">
                <input type="text" class="rule-content-item" placeholder="Content item 2">
                <button class="remove-content">&times;</button>
              </div>
              <div class="content-input">
                <input type="text" class="rule-content-item" placeholder="Content item 3">
                <button class="remove-content">&times;</button>
              </div>
              <div class="content-input">
                <input type="text" class="rule-content-item" placeholder="Content item 4">
                <button class="remove-content">&times;</button>
              </div>
            </div>
          </div>
          <button class="add-content-btn" id="add-content-field">
            <i class="fas fa-plus"></i> Add Another Content Field
          </button>
        </div>

        <div class="form-group">
          <button id="add-rule"><i class="fas fa-plus"></i> Add Rule</button>
          <button id="update-rules"><i class="fas fa-sync-alt"></i> Refresh Rules</button>
        </div>
      </div>

      <h2>Current Rules</h2>
      <div class="rules-list" id="admin-rules-container">
        <div class="loading">Loading rules...</div>
      </div>

      <a href="hotel-rules-user.html" class="user-link">View User Page</a>
    </div>
  </div>

  <div class="notification" id="notification"></div>

  <script>
  const API_BASE = 'api.php'; // Update this path if needed

  document.addEventListener('DOMContentLoaded', function() {
    // Show notification
    function showNotification(message, isError = false) {
      const notification = document.getElementById('notification');
      notification.textContent = message;
      notification.className = isError ? 'notification error-notification' : 'notification';
      notification.style.display = 'block';

      setTimeout(() => {
        notification.style.display = 'none';
      }, 3000);
    }

    // Load categories from API
    async function loadCategories() {
      try {
        const response = await fetch(`${API_BASE}?request=categories`);
        const categories = await response.json();

        const categorySelect = document.getElementById('rule-category');
        categorySelect.innerHTML = '<option value="">Select a category</option>';

        categories.forEach(category => {
          const option = document.createElement('option');
          option.value = category.id;
          option.textContent = category.name;
          categorySelect.appendChild(option);
        });
      } catch (error) {
        console.error('Error loading categories:', error);
        showNotification('Failed to load categories', true);
      }
    }

    // Load rules from API
    async function loadRules() {
      const adminContainer = document.getElementById('admin-rules-container');
      adminContainer.innerHTML = '<div class="loading">Loading rules...</div>';

      try {
        const response = await fetch(`${API_BASE}?request=rules`);
        const rules = await response.json();

        if (rules.error) {
          throw new Error(rules.error);
        }

        renderAdminRules(rules);
      } catch (error) {
        console.error('Error loading rules:', error);
        adminContainer.innerHTML = '<div class="loading">Error loading rules. Please try again.</div>';
        showNotification('Failed to load rules', true);
      }
    }

    // Render rules in admin view
    function renderAdminRules(rules) {
      const adminContainer = document.getElementById('admin-rules-container');

      if (rules.length === 0) {
        adminContainer.innerHTML = '<p>No rules added yet. Use the form above to add rules.</p>';
        return;
      }

      // Group rules by category
      const rulesByCategory = {};
      rules.forEach(rule => {
        if (!rulesByCategory[rule.category_name]) {
          rulesByCategory[rule.category_name] = [];
        }
        rulesByCategory[rule.category_name].push(rule);
      });

      adminContainer.innerHTML = '';

      for (const [categoryName, categoryRules] of Object.entries(rulesByCategory)) {
        const categoryElement = document.createElement('div');
        categoryElement.className = 'rules-section';
        categoryElement.innerHTML = `<h3>${categoryName}</h3>`;

        categoryRules.forEach(rule => {
          let contentItemsHTML = '';
          rule.items.forEach(item => {
            contentItemsHTML += `<div class="rule-content">• ${item}</div>`;
          });

          const ruleElement = document.createElement('div');
          ruleElement.className = 'rule-item';
          ruleElement.dataset.id = rule.id;
          ruleElement.innerHTML = `
                            <div class="rule-category">${rule.category_name}</div>
                            <div class="rule-title"><strong>${rule.title}</strong></div>
                            ${contentItemsHTML}
                            <button class="delete-rule" data-id="${rule.id}">
                                <i class="fas fa-trash"></i> Delete Rule
                            </button>
                        `;
          categoryElement.appendChild(ruleElement);
        });

        adminContainer.appendChild(categoryElement);
      }

      // Add event listeners to delete buttons
      document.querySelectorAll('.delete-rule').forEach(button => {
        button.addEventListener('click', async function() {
          const id = this.getAttribute('data-id');
          await deleteRule(id);
        });
      });
    }

    // Add content field functionality
    document.getElementById('add-content-field').addEventListener('click', function() {
      const contentInputs = document.querySelector('.content-inputs');
      const newInput = document.createElement('div');
      newInput.className = 'content-input';
      newInput.innerHTML = `
                    <input type="text" class="rule-content-item" placeholder="Additional content item">
                    <button class="remove-content">&times;</button>
                `;
      contentInputs.appendChild(newInput);

      // Add event listener to the new remove button
      newInput.querySelector('.remove-content').addEventListener('click', function() {
        newInput.remove();
      });
    });

    // Add event listeners to existing remove buttons
    document.querySelectorAll('.remove-content').forEach(button => {
      button.addEventListener('click', function() {
        this.parentElement.remove();
      });
    });

    // Add new rule
    document.getElementById('add-rule').addEventListener('click', async function() {
      const categoryId = document.getElementById('rule-category').value;
      const title = document.getElementById('rule-title').value.trim();
      const contentItems = Array.from(document.querySelectorAll('.rule-content-item'))
        .map(input => input.value.trim())
        .filter(value => value !== '');

      if (!categoryId) {
        showNotification('Please select a category', true);
        return;
      }

      if (!title) {
        showNotification('Please enter a rule title', true);
        return;
      }

      if (contentItems.length === 0) {
        showNotification('Please add at least one content item', true);
        return;
      }

      await addRule(categoryId, title, contentItems);
    });

    // Refresh rules
    document.getElementById('update-rules').addEventListener('click', function() {
      loadRules();
    });

    // Add rule function
    async function addRule(categoryId, title, contentItems) {
      try {
        const response = await fetch(`${API_BASE}?request=add_rule`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            category_id: categoryId,
            title: title,
            items: contentItems
          })
        });

        const result = await response.json();

        if (result.error) {
          throw new Error(result.error);
        }

        // Clear form
        document.getElementById('rule-title').value = '';
        document.querySelectorAll('.rule-content-item').forEach(input => {
          input.value = '';
        });

        // Reset to 4 content fields
        const contentInputs = document.querySelector('.content-inputs');
        contentInputs.innerHTML = `
                        <div class="content-input">
                            <input type="text" class="rule-content-item" placeholder="Content item 1">
                            <button class="remove-content">&times;</button>
                        </div>
                        <div class="content-input">
                            <input type="text" class="rule-content-item" placeholder="Content item 2">
                            <button class="remove-content">&times;</button>
                        </div>
                        <div class="content-input">
                            <input type="text" class="rule-content-item" placeholder="Content item 3">
                            <button class="remove-content">&times;</button>
                        </div>
                        <div class="content-input">
                            <input type="text" class="rule-content-item" placeholder="Content item 4">
                            <button class="remove-content">&times;</button>
                        </div>
                    `;

        // Add event listeners to new remove buttons
        document.querySelectorAll('.remove-content').forEach(button => {
          button.addEventListener('click', function() {
            this.parentElement.remove();
          });
        });

        showNotification('Rule added successfully!');
        loadRules(); // Refresh the rules list
      } catch (error) {
        console.error('Error adding rule:', error);
        showNotification('Failed to add rule: ' + error.message, true);
      }
    }

    // Delete rule function
    async function deleteRule(id) {
      if (!confirm('Are you sure you want to delete this rule?')) {
        return;
      }

      try {
        const response = await fetch(`${API_BASE}?request=delete_rule&id=${id}`, {
          method: 'DELETE'
        });

        const result = await response.json();

        if (result.error) {
          throw new Error(result.error);
        }

        showNotification('Rule deleted successfully!');
        loadRules(); // Refresh the rules list
      } catch (error) {
        console.error('Error deleting rule:', error);
        showNotification('Failed to delete rule: ' + error.message, true);
      }
    }

    // Initial load
    loadCategories();
    loadRules();
  });
  </script>
</body>

</html>