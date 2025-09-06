<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hotel Rules Management - Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles/style.css">

</head>
<?php include 'sidebar.php'; ?>

<body>
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
  const API_BASE = 'api/hotels_rule.php';

  document.addEventListener('DOMContentLoaded', function() {
    function showNotification(message, isError = false) {
      const notification = document.getElementById('notification');
      notification.textContent = message;
      notification.className = isError ? 'notification error-notification' : 'notification';
      notification.style.display = 'block';

      setTimeout(() => {
        notification.style.display = 'none';
      }, 3000);
    }

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

    function renderAdminRules(rules) {
      const adminContainer = document.getElementById('admin-rules-container');

      if (rules.length === 0) {
        adminContainer.innerHTML = '<p>No rules added yet. Use the form above to add rules.</p>';
        return;
      }
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
      document.querySelectorAll('.delete-rule').forEach(button => {
        button.addEventListener('click', async function() {
          const id = this.getAttribute('data-id');
          await deleteRule(id);
        });
      });
    }

    document.getElementById('add-content-field').addEventListener('click', function() {
      const contentInputs = document.querySelector('.content-inputs');
      const newInput = document.createElement('div');
      newInput.className = 'content-input';
      newInput.innerHTML = `
                    <input type="text" class="rule-content-item" placeholder="Additional content item">
                    <button class="remove-content">&times;</button>
                `;
      contentInputs.appendChild(newInput);
      newInput.querySelector('.remove-content').addEventListener('click', function() {
        newInput.remove();
      });
    });
    document.querySelectorAll('.remove-content').forEach(button => {
      button.addEventListener('click', function() {
        this.parentElement.remove();
      });
    });
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
    document.getElementById('update-rules').addEventListener('click', function() {
      loadRules();
    });
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
        document.getElementById('rule-title').value = '';
        document.querySelectorAll('.rule-content-item').forEach(input => {
          input.value = '';
        });
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

        document.querySelectorAll('.remove-content').forEach(button => {
          button.addEventListener('click', function() {
            this.parentElement.remove();
          });
        });

        showNotification('Rule added successfully!');
        loadRules();
      } catch (error) {
        console.error('Error adding rule:', error);
        showNotification('Failed to add rule: ' + error.message, true);
      }
    }
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
        loadRules();
      } catch (error) {
        console.error('Error deleting rule:', error);
        showNotification('Failed to delete rule: ' + error.message, true);
      }
    }
    loadCategories();
    loadRules();
  });
  </script>
</body>

</html>