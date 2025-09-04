<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Voucher & Promotion Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/style.css">
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <header>
            <h1><i class="fas fa-ticket-alt"></i> Complete Voucher & Promotion Management</h1>
            <p>Create and manage discount vouchers and promotions for your booking platform</p>
        </header>
        <div class="stats">
            <div class="stat-box">
                <h3>Total Vouchers</h3>
                <div class="stat-number" id="total-vouchers">0</div>
            </div>
            <div class="stat-box">
                <h3>Active Vouchers</h3>
                <div class="stat-number" id="active-vouchers">0</div>
            </div>
            <div class="stat-box">
                <h3>Total Discount</h3>
                <div class="stat-number" id="total-discount">$0</div>
            </div>
            <div class="stat-box">
                <h3>Active Promotions</h3>
                <div class="stat-number" id="active-promotions">0</div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="tabs-navigation">
            <div class="tab-link active" data-tab="vouchers">Voucher Management</div>
            <div class="tab-link" data-tab="promotions">Promotion System</div>
        </div>

        <!-- Vouchers Tab -->
        <div class="tab-content active" id="vouchers-tab">
            <section class="form-sectionn">
                <h2>Create New Voucher</h2>
                <form id="voucher-form">
                    <div class="form-group">
                        <label for="voucher-codes">Voucher Codes (one per line)</label>
                        <textarea id="voucher-codes" placeholder="Enter your custom voucher codes, one per line"
                            required></textarea>
                        <div class="codes-hint">You can enter one or more voucher codes. Each code should be on a
                            separate line.</div>
                    </div>
                    <div class="form-group">
                        <label for="discount-type">Discount Type</label>
                        <select id="discount-type">
                            <option value="percentage">Percentage Discount</option>
                            <option value="fixed">Fixed Amount Discount</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="discount-value">Discount Value</label>
                        <input type="number" id="discount-value" min="1" max="100" value="10" required>
                    </div>

                    <div class="form-group">
                        <label for="expiry-date">Expiry Date</label>
                        <input type="date" id="expiry-date" required>
                    </div>

                    <div class="form-group">
                        <label for="max-usage">Maximum Usage Count</label>
                        <input type="number" id="max-usage" min="1" value="100" required>
                    </div>

                    <button type="submit" class="btn btn-generate">
                        <i class="fas fa-magic"></i> Generate Vouchers
                    </button>
                </form>
            </section>

            <section class="vouchers-section">
                <h2>Generated Vouchers</h2>
                <div class="voucher-list" id="voucher-list">
                    <div class="empty-state">
                        <i class="fas fa-ticket-alt"></i>
                        <p>No vouchers created yet</p>
                        <p>Create vouchers using the form</p>
                    </div>
                </div>
            </section>

            <section class="instructions">
                <h2>Integration Instructions</h2>
                <ol>
                    <li>Create vouchers using the form on this page</li>
                    <li>Copy the voucher code using the "Copy Code" button</li>
                    <li>Integrate the voucher code into your booking system by using the following API endpoint:</li>
                </ol>

                <div class="integration-code">
                    // Example API endpoint for voucher validation
                    POST /api/validate-voucher
                    Content-Type: application/json

                    {
                    "voucherCode": "SUMMER15",
                    "bookingTotal": 150.00
                    }
                    {
                    "valid": true,
                    "discountAmount": 22.50,
                    "newTotal": 127.50
                    }
                </div>

                <p style="margin-top: 15px;">For full integration documentation, please refer to the developer portal.
                </p>
            </section>
        </div>

        <!-- Promotions Tab -->
        <div class="tab-content" id="promotions-tab">
            <h2 class="mb-4">Create New Promotion</h2>
            <div class="promotion-tabs">
                <div class="promotion-tab active" data-type="last-minute">
                    <i class="fas fa-bolt"></i>
                    <h4>Last Minute</h4>
                    <p>For bookings 0-3 days before check-in</p>
                </div>
                <div class="promotion-tab" data-type="early-bird">
                    <i class="fas fa-earlybirds"></i>
                    <h4>Early Bird</h4>
                    <p>For bookings 5+ days before check-in</p>
                </div>
                <div class="promotion-tab" data-type="long-stay">
                    <i class="fas fa-calendar-week"></i>
                    <h4>Long Stay</h4>
                    <p>Discounts for longer stays</p>
                </div>
            </div>
            <div class="promotion-form-card" id="last-minute-promo">
                <div class="promotion-form-header">
                    <h3 class="m-0"><i class="fas fa-bolt me-2"></i> Create Last Minute Promotion</h3>
                    <span class="discount-badge">0-3 days before check-in</span>
                </div>

                <div class="promotion-form-body">
                    <p class="mb-4">Sell empty rooms by offering discount, which can be booked up to 2 days before
                        check-in</p>

                    <div class="form-sectionn">
                        <h4>Set Discount</h4>

                        <div class="form-group">
                            <label for="last-minute-discount-type">Discount Type</label>
                            <select id="last-minute-discount-type" class="promo-discount-type">
                                <option value="percentage">Percentage Discount</option>
                                <option value="fixed">Fixed Amount Discount</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="last-minute-discount-value">Discount Value</label>
                            <input type="number" id="last-minute-discount-value" class="promo-discount-value" min="1"
                                max="100" value="20" required>
                        </div>
                        <div class="form-group">
                            <label for="last-minute-additional-discount">Additional discount for logged-in users
                                only</label>
                            <input type="number" id="last-minute-additional-discount" class="form-control"
                                placeholder="Enter additional discount" min="0" max="50">
                            <div class="codes-hint">Optional: Extra discount for registered users</div>
                        </div>
                    </div>

                    <div class="form-sectionn">
                        <h4>Choose Bookable Period</h4>
                        <p>How far in advance do you wish to get bookings under this promotion?</p>

                        <div class="period-options">
                            <div class="period-option">
                                <p>Same Day Check-in</p>
                            </div>
                            <div class="period-option">
                                <p>Up to 1 Day before Check-in</p>
                            </div>
                            <div class="period-option selected">
                                <p>Up to 2 Days before Check-in</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-sectionn">
                        <h4>Promotion Status</h4>
                        <div class="form-group">
                            <label class="toggle-label">
                                <span class="toggle-switch">
                                    <input type="checkbox" id="last-minute-active" checked>
                                    <span class="toggle-slider"></span>
                                </span>
                                <span>Active Promotion</span>
                            </label>
                            <div class="codes-hint">Toggle to activate or deactivate this promotion</div>
                        </div>
                    </div>

                    <div class="form-sectionn">
                        <h4>Promotion Name</h4>
                        <div class="form-group">
                            <input type="text" class="form-control promo-name-input" value="Last Minute Promotion">
                            <div class="codes-hint">This name will be used for reporting and identification</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </button>
                        <button class="btn btn-generate" type="button" onclick="createPromotion('last-minute')">
                            <i class="fas fa-check me-2"></i> Create Promotion
                        </button>
                    </div>
                </div>
            </div>

            <!-- Early Bird Promotion Form -->
            <div class="promotion-form-card" id="early-bird-promo" style="display: none;">
                <div class="promotion-form-header">
                    <h3 class="m-0"><i class="fas fa-earlybirds me-2"></i> Create Early Bird Promotion</h3>
                    <span class="discount-badge">5+ days before check-in</span>
                </div>

                <div class="promotion-form-body">
                    <p class="mb-4">Get advance bookings from customers by offering discount to bookings made long
                        before check-in.</p>

                    <div class="form-sectionn">
                        <h4>Set Discount</h4>

                        <div class="form-group">
                            <label for="early-bird-discount-type">Discount Type</label>
                            <select id="early-bird-discount-type" class="promo-discount-type">
                                <option value="percentage">Percentage Discount</option>
                                <option value="fixed">Fixed Amount Discount</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="early-bird-discount-value">Discount Value</label>
                            <input type="number" id="early-bird-discount-value" class="promo-discount-value" min="1"
                                max="100" value="30" required>
                        </div>

                        <div class="form-group">
                            <label for="early-bird-additional-discount">Additional discount for logged-in users
                                only</label>
                            <input type="number" id="early-bird-additional-discount" class="form-control" value="10"
                                min="0" max="50">
                            <div class="codes-hint">Optional: Extra discount for registered users</div>
                        </div>
                    </div>

                    <div class="form-sectionn">
                        <h4>Choose Bookable Period</h4>
                        <p>How far in advance do you wish to get bookings under this promotion?</p>

                        <div class="period-options">
                            <div class="period-option selected">
                                <p>5 days or more</p>
                            </div>
                            <div class="period-option">
                                <p>7 days or more</p>
                            </div>
                            <div class="period-option">
                                <p>14 days or more</p>
                            </div>
                            <div class="period-option">
                                <p>21 days or more</p>
                            </div>
                            <div class="period-option">
                                <p>30 days or more</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-sectionn">
                        <h4>Promotion Status</h4>
                        <div class="form-group">
                            <label class="toggle-label">
                                <span class="toggle-switch">
                                    <input type="checkbox" id="early-bird-active" checked>
                                    <span class="toggle-slider"></span>
                                </span>
                                <span>Active Promotion</span>
                            </label>
                            <div class="codes-hint">Toggle to activate or deactivate this promotion</div>
                        </div>
                    </div>

                    <div class="form-sectionn">
                        <h4>Promotion Name</h4>
                        <div class="form-group">
                            <input type="text" class="form-control promo-name-input" value="Early Bird Promotion">
                            <div class="codes-hint">This name will be used for reporting and identification</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </button>
                        <button class="btn btn-generate" type="button" onclick="createPromotion('early-bird')">
                            <i class="fas fa-check me-2"></i> Create Promotion
                        </button>
                    </div>
                </div>
            </div>
            <div class="promotion-form-card" id="long-stay-promo" style="display: none;">
                <div class="promotion-form-header">
                    <h3 class="m-0"><i class="fas fa-calendar-week me-2"></i> Create Long Stay Promotion</h3>
                    <span class="discount-badge">Target longer stays</span>
                </div>

                <div class="promotion-form-body">
                    <p class="mb-4">Target higher occupancy for longer stays with exclusive promotions</p>

                    <div class="form-sectionn">
                        <h4>Choose how you want to offer the promotion</h4>

                        <div class="discount-options">
                            <div class="discount-option selected" id="long-stay-discount-option">
                                <i class="fas fa-percent fa-2x mb-2"></i>
                                <h5>I want to offer discount</h5>
                                <p>Offer discount to users booking longer stays</p>
                            </div>
                            <div class="discount-option" id="long-stay-free-nights-option">
                                <i class="fas fa-moon fa-2x mb-2"></i>
                                <h5>I want to offer free night(s)</h5>
                                <p>Offer free nights to users booking longer stays</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-sectionn" id="long-stay-discount-options">
                        <h4>Set Discount</h4>

                        <div class="form-group">
                            <label for="long-stay-discount-type">Discount Type</label>
                            <select id="long-stay-discount-type" class="promo-discount-type">
                                <option value="percentage">Percentage Discount</option>
                                <option value="fixed">Fixed Amount Discount</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="long-stay-discount-value">Discount Value</label>
                            <input type="number" id="long-stay-discount-value" class="promo-discount-value" min="1"
                                max="100" value="15" required>
                        </div>
                    </div>

                    <!-- Free Nights Options -->
                    <div class="form-sectionn" id="long-stay-free-nights-options" style="display: none;">
                        <h4>Set Free Night Rule</h4>

                        <div class="free-night-rule">
                            <div class="free-night-option">
                                <p>Stay for 2 nights and pay for 1 night</p>
                            </div>
                            <div class="free-night-option">
                                <p>Stay for 3 nights and pay for 2 nights</p>
                            </div>
                            <div class="free-night-option selected">
                                <p>Stay for 4 nights and pay for 3 nights</p>
                            </div>
                            <div class="free-night-option">
                                <p>Set custom rule</p>
                            </div>
                        </div>
                        <div class="custom-rule-inputs">
                            <span>Stay for</span>
                            <input type="number" class="form-control" value="2" min="1">
                            <span>nights and pay for</span>
                            <input type="number" class="form-control" value="1" min="1">
                            <span>night(s)</span>
                        </div>
                    </div>

                    <div class="form-sectionn">
                        <h4>Choose Minimum Stay Duration</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-4 mb-2">
                                            <div class="custom-radio text-center">
                                                <input type="radio" name="minStay" id="min2" class="form-check-input">
                                                <label class="form-check-label" for="min2">2 days or more</label>
                                            </div>
                                        </div>
                                        <div class="col-4 mb-2">
                                            <div class="custom-radio text-center">
                                                <input type="radio" name="minStay" id="min3" class="form-check-input">
                                                <label class="form-check-label" for="min3">3 days or more</label>
                                            </div>
                                        </div>
                                        <div class="col-4 mb-2">
                                            <div class="custom-radio text-center selected">
                                                <input type="radio" name="minStay" id="min4" class="form-check-input"
                                                    checked>
                                                <label class="form-check-label" for="min4">4 days or more</label>
                                            </div>
                                        </div>
                                        <div class="col-4 mb-2">
                                            <div class="custom-radio text-center">
                                                <input type="radio" name="minStay" id="min5" class="form-check-input">
                                                <label class="form-check-label" for="min5">5 days or more</label>
                                            </div>
                                        </div>
                                        <div class="col-4 mb-2">
                                            <div class="custom-radio text-center">
                                                <input type="radio" name="minStay" id="min7" class="form-check-input">
                                                <label class="form-check-label" for="min7">7 days or more</label>
                                            </div>
                                        </div>
                                        <div class="col-4 mb-2">
                                            <div class="custom-radio text-center">
                                                <input type="radio" name="minStay" id="minCustom"
                                                    class="form-check-input">
                                                <label class="form-check-label" for="minCustom">Set custom
                                                    duration</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-sectionn">
                        <h4>Promotion Status</h4>
                        <div class="form-group">
                            <label class="toggle-label">
                                <span class="toggle-switch">
                                    <input type="checkbox" id="long-stay-active" checked>
                                    <span class="toggle-slider"></span>
                                </span>
                                <span>Active Promotion</span>
                            </label>
                            <div class="codes-hint">Toggle to activate or deactivate this promotion</div>
                        </div>
                    </div>

                    <div class="form-sectionn">
                        <h4>Promotion Name</h4>
                        <div class="form-group">
                            <input type="text" class="form-control promo-name-input" value="LOS-FRN-4n1">
                            <div class="codes-hint">This name will be used for reporting and identification</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </button>
                        <button class="btn btn-generate" type="button" onclick="createPromotion('long-stay')">
                            <i class="fas fa-check me-2"></i> Create Promotion
                        </button>
                    </div>
                </div>
            </div>

            <!-- Generated Promotions Section -->
            <section class="vouchers-section">
                <h2>Generated Promotions</h2>
                <div class="promotion-list" id="promotion-list">
                    <div class="empty-state">
                        <i class="fas fa-tags"></i>
                        <p>No promotions created yet</p>
                        <p>Create promotions using the form above</p>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = window.location.origin + '/tripsorus/tripsorus-admin/api/vouchers_sub.php';
        async function apiCall(endpoint, method = 'GET', data = null) {
            const options = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                }
            };

            if (data && (method === 'POST' || method === 'PUT')) {
                options.body = JSON.stringify(data);
            }

            try {
                const response = await fetch(`${API_BASE}/${endpoint}`, options);

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.error || `API error: ${response.status}`);
                }

                return await response.json();
            } catch (error) {
                console.error('API call failed:', error);
                throw error;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const defaultExpiry = new Date();
            defaultExpiry.setDate(defaultExpiry.getDate() + 30);
            document.getElementById('expiry-date').valueAsDate = defaultExpiry;
            loadVouchers();
            loadPromotions();
            document.getElementById('voucher-form').addEventListener('submit', function (e) {
                e.preventDefault();
                generateVouchers();
            });
            document.getElementById('discount-type').addEventListener('change', function () {
                const discountValue = document.getElementById('discount-value');
                if (this.value === 'percentage') {
                    discountValue.min = 1;
                    discountValue.max = 100;
                    discountValue.value = 10;
                } else {
                    discountValue.min = 1;
                    discountValue.max = 1000;
                    discountValue.value = 20;
                }
            });
            document.querySelectorAll('.promo-discount-type').forEach(select => {
                select.addEventListener('change', function () {
                    const discountValue = this.closest('.form-sectionn').querySelector('.promo-discount-value');
                    if (this.value === 'percentage') {
                        discountValue.min = 1;
                        discountValue.max = 100;
                    } else {
                        discountValue.min = 1;
                        discountValue.max = 1000;
                    }
                });
            });

            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');
            tabLinks.forEach(link => {
                link.addEventListener('click', function () {
                    const tabId = this.getAttribute('data-tab');
                    tabLinks.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    tabContents.forEach(content => {
                        if (content.id === `${tabId}-tab`) {
                            content.classList.add('active');
                        } else {
                            content.classList.remove('active');
                        }
                    });
                });
            });
            const promotionTabs = document.querySelectorAll('.promotion-tab');
            const promotionForms = {
                'last-minute': document.getElementById('last-minute-promo'),
                'early-bird': document.getElementById('early-bird-promo'),
                'long-stay': document.getElementById('long-stay-promo')
            };

            promotionTabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    const type = this.getAttribute('data-type');
                    promotionTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    Object.keys(promotionForms).forEach(key => {
                        if (key === type) {
                            promotionForms[key].style.display = 'block';
                        } else {
                            promotionForms[key].style.display = 'none';
                        }
                    });
                });
            });
            const longStayDiscountOption = document.getElementById('long-stay-discount-option');
            const longStayFreeNightsOption = document.getElementById('long-stay-free-nights-option');
            const longStayDiscountOptions = document.getElementById('long-stay-discount-options');
            const longStayFreeNightsOptions = document.getElementById('long-stay-free-nights-options');

            longStayDiscountOption.addEventListener('click', function () {
                longStayDiscountOption.classList.add('selected');
                longStayFreeNightsOption.classList.remove('selected');
                longStayDiscountOptions.style.display = 'block';
                longStayFreeNightsOptions.style.display = 'none';
            });

            longStayFreeNightsOption.addEventListener('click', function () {
                longStayFreeNightsOption.classList.add('selected');
                longStayDiscountOption.classList.remove('selected');
                longStayFreeNightsOptions.style.display = 'block';
                longStayDiscountOptions.style.display = 'none';
            });
            const periodOptions = document.querySelectorAll('.period-option');
            periodOptions.forEach(option => {
                option.addEventListener('click', function () {
                    if (this.closest('.period-options')) {
                        this.closest('.period-options').querySelectorAll('.period-option').forEach(o => o.classList.remove('selected'));
                    }
                    this.classList.add('selected');
                });
            });

            const freeNightOptions = document.querySelectorAll('.free-night-option');
            freeNightOptions.forEach(option => {
                option.addEventListener('click', function () {
                    if (this.closest('.free-night-rule')) {
                        this.closest('.free-night-rule').querySelectorAll('.free-night-option').forEach(o => o.classList.remove('selected'));
                    }
                    this.classList.add('selected');
                });
            });
        });

        // Generate vouchers
        async function generateVouchers() {
            const discountType = document.getElementById('discount-type').value;
            const discountValue = document.getElementById('discount-value').value;
            const expiryDate = document.getElementById('expiry-date').value;
            const maxUsage = document.getElementById('max-usage').value;

            const codesText = document.getElementById('voucher-codes').value;
            if (!codesText.trim()) {
                alert('Please enter at least one voucher code');
                return;
            }
            const codes = codesText.split('\n').map(code => code.trim()).filter(code => code.length > 0);
            const vouchers = codes.map(code => ({
                code,
                discountType,
                discountValue: parseFloat(discountValue),
                expiryDate,
                maxUsage: parseInt(maxUsage)
            }));

            try {
                await apiCall('vouchers', 'POST', { vouchers });
                document.getElementById('voucher-codes').value = '';
                loadVouchers();

                alert('Vouchers saved successfully!');
            } catch (error) {
                alert('Error creating vouchers: ' + error.message);
            }
        }
        async function loadVouchers() {
            try {
                const vouchers = await apiCall('vouchers');
                renderVouchers(vouchers);
            } catch (error) {
                console.error('Failed to load vouchers:', error);
                document.getElementById('voucher-list').innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Failed to load vouchers</p>
                        <p>Please try again later</p>
                    </div>`;
            }
        }

        // Render vouchers in UI
        function renderVouchers(vouchers) {
            const voucherList = document.getElementById('voucher-list');

            // Update stats
            document.getElementById('total-vouchers').textContent = vouchers.length;

            const activeVouchers = vouchers.filter(v => new Date(v.expiry_date) > new Date()).length;
            document.getElementById('active-vouchers').textContent = activeVouchers;

            let totalDiscount = vouchers.reduce((sum, v) => v.discount_type === 'percentage' ? sum + parseInt(v.discount_value) : sum, 0);
            document.getElementById('total-discount').textContent = `${totalDiscount}%`;

            if (vouchers.length === 0) {
                voucherList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-ticket-alt"></i>
                        <p>No vouchers created yet</p>
                        <p>Create vouchers using the form</p>
                    </div>`;
                return;
            }

            voucherList.innerHTML = '';
            vouchers.forEach((v) => {
                addVoucherToUI(v);
            });
        }

        // Add voucher to UI
        function addVoucherToUI(v) {
            const voucherList = document.getElementById('voucher-list');
            const expiry = new Date(v.expiry_date).toLocaleDateString();
            const discountDisplay = v.discount_type === 'percentage' ? `${v.discount_value}% OFF` : `$${v.discount_value} OFF`;

            const card = document.createElement('div');
            card.className = 'voucher-card';
            card.innerHTML = `
                <div class="voucher-header">
                    <div class="voucher-code">${v.code}</div>
                    <div class="voucher-discount">${discountDisplay}</div>
                </div>
                <div class="voucher-details">
                    <p><i class="far fa-calendar-alt"></i> Expires: ${expiry}</p>
                    <p><i class="fas fa-users"></i> Usage: ${v.used_count} / ${v.max_usage}</p>
                    <p><i class="far fa-clock"></i> Created: ${new Date(v.created_at).toLocaleDateString()}</p>
                </div>
                <div class="voucher-actions">
                    <button class="btn-copy" onclick="copyCode('${v.code}')"><i class="far fa-copy"></i> Copy Code</button>
                    <button class="btn btn-outline-danger" onclick="deleteVoucher('${v.id}')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            `;
            voucherList.appendChild(card);
        }

        // Delete voucher
        async function deleteVoucher(id) {
            if (confirm('Are you sure you want to delete this voucher?')) {
                try {
                    await apiCall(`vouchers/${id}`, 'DELETE');
                    loadVouchers();
                } catch (error) {
                    alert('Error deleting voucher: ' + error.message);
                }
            }
        }

        // Create promotion
        async function createPromotion(type) {
            let discountType, discountValue, promoName, isActive, minStayDays, daysBeforeCheckin, additionalDiscount;

            switch (type) {
                case 'last-minute':
                    discountType = document.getElementById('last-minute-discount-type').value;
                    discountValue = document.getElementById('last-minute-discount-value').value;
                    promoName = document.querySelector('#last-minute-promo .promo-name-input').value;
                    isActive = document.getElementById('last-minute-active').checked;
                    daysBeforeCheckin = 2
                    additionalDiscount = document.getElementById('last-minute-additional-discount').value || 0;
                    break;
                case 'early-bird':
                    discountType = document.getElementById('early-bird-discount-type').value;
                    discountValue = document.getElementById('early-bird-discount-value').value;
                    promoName = document.querySelector('#early-bird-promo .promo-name-input').value;
                    isActive = document.getElementById('early-bird-active').checked;
                    daysBeforeCheckin = 5; 
                    additionalDiscount = document.getElementById('early-bird-additional-discount').value || 0;
                    break;
                case 'long-stay':
                    discountType = document.getElementById('long-stay-discount-type').value;
                    discountValue = document.getElementById('long-stay-discount-value').value;
                    promoName = document.querySelector('#long-stay-promo .promo-name-input').value;
                    isActive = document.getElementById('long-stay-active').checked;
                    minStayDays = 4;
                    break;
            }
            const promotion = {
                name: promoName,
                type: type,
                discountType: discountType,
                discountValue: parseFloat(discountValue),
                status: isActive ? 'active' : 'inactive',
                minStayDays: minStayDays || null,
                daysBeforeCheckin: daysBeforeCheckin || null,
                additionalDiscount: additionalDiscount ? parseFloat(additionalDiscount) : 0
            };

            try {
                const result = await apiCall('promotions', 'POST', { promotion });
                loadPromotions();

                const discountDisplay = discountType === 'percentage' ? `${discountValue}% OFF` : `$${discountValue} OFF`;
                alert(`Promotion "${promoName}" created successfully with ${discountDisplay} discount!`);
            } catch (error) {
                alert('Error creating promotion: ' + error.message);
            }
        }
        async function loadPromotions() {
            try {
                const promotions = await apiCall('promotions');
                renderPromotions(promotions);
            } catch (error) {
                console.error('Failed to load promotions:', error);
                document.getElementById('promotion-list').innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Failed to load promotions</p>
                        <p>Please try again later</p>
                    </div>`;
            }
        }
        function renderPromotions(promotions) {
            const promotionList = document.getElementById('promotion-list');
            const activePromotions = promotions.filter(p => p.status === 'active').length;
            document.getElementById('active-promotions').textContent = activePromotions;

            if (promotions.length === 0) {
                promotionList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-tags"></i>
                        <p>No promotions created yet</p>
                        <p>Create promotions using the form above</p>
                    </div>`;
                return;
            }

            promotionList.innerHTML = '';
            promotions.forEach((p) => {
                addPromotionToUI(p);
            });
        }

        function addPromotionToUI(p) {
            const promotionList = document.getElementById('promotion-list');
            const discountDisplay = p.discount_type === 'percentage' ? `${p.discount_value}% OFF` : `$${p.discount_value} OFF`;
            const statusClass = p.status === 'active' ? 'success' : 'secondary';

            const card = document.createElement('div');
            card.className = 'promotion-card-item';
            card.innerHTML = `
                <div class="promotion-header">
                    <div class="voucher-code">${p.name}</div>
                    <span class="badge bg-${statusClass}">${p.status}</span>
                </div>
                <div class="promotion-details">
                    <p><i class="fas fa-tag"></i> Type: ${p.type.replace('-', ' ')}</p>
                    <p><i class="fas fa-percent"></i> Discount: ${discountDisplay}</p>
                    <p><i class="far fa-clock"></i> Created: ${new Date(p.created_at).toLocaleDateString()}</p>
                </div>
                <div class="promotion-actions">
                    <button class="btn-copy" onclick="togglePromotionStatus('${p.id}', '${p.status}')">
                        <i class="fas fa-power-off"></i> ${p.status === 'active' ? 'Deactivate' : 'Activate'}
                    </button>
                    <button class="btn btn-outline-danger" onclick="deletePromotion('${p.id}')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            `;
            promotionList.appendChild(card);
        }
        async function togglePromotionStatus(id, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

            try {
                await apiCall(`promotions/${id}`, 'PUT', { status: newStatus });
                loadPromotions();
            } catch (error) {
                alert('Error updating promotion status: ' + error.message);
            }
        }
        async function deletePromotion(id) {
            if (confirm('Are you sure you want to delete this promotion?')) {
                try {
                    await apiCall(`promotions/${id}`, 'DELETE');
                    loadPromotions();
                } catch (error) {
                    alert('Error deleting promotion: ' + error.message);
                }
            }
        }
        function copyCode(code) {
            navigator.clipboard.writeText(code)
                .then(() => {
                    alert(`Copied: ${code}`);
                })
                .catch(err => {
                    console.error('Failed to copy: ', err);
                });
        }
        async function validateVoucher(voucherCode, bookingTotal) {
            try {
                const result = await apiCall('validate-voucher', 'POST', {
                    voucherCode,
                    bookingTotal
                });
                return result;
            } catch (error) {
                return { valid: false, message: error.message };
            }
        }
        async function applyPromotions(bookingDate, checkInDate, stayDuration, isLoggedIn = false) {
            try {
                const result = await apiCall('apply-promotions', 'POST', {
                    bookingDate,
                    checkInDate,
                    stayDuration,
                    isLoggedIn
                });
                return result;
            } catch (error) {
                console.error('Error applying promotions:', error);
                return { applicablePromotions: [] };
            }
        }
    </script>
</body>

</html>