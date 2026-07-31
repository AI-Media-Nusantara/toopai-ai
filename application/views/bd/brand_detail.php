<div class="detail-container">
    <div class="page-header">
        <a href="<?= base_url('bd/brands') ?>" class="back-link">← Back to Brands</a>
        <h1 class="page-title"><?= htmlspecialchars($brand->name) ?></h1>
        <p class="page-subtitle">Brand Detail & Performance Overview</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">Rp <?= number_format($brand->total_gmv, 0, ',', '.') ?></div>
            <div class="stat-label">Total GMV</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= number_format($brand->total_orders) ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= number_format($brand->total_creators) ?></div>
            <div class="stat-label">Creators</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= htmlspecialchars($brand->category) ?></div>
            <div class="stat-label">Category</div>
        </div>
    </div>

    <!-- Products Section - Menggunakan affiliate_products -->
    <div class="section-card">
        <div class="section-header">
            <h2 class="section-title">📦 Products</h2>
            <button class="btn-add-product" onclick="showAddProductModal()">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </div>
        <div class="products-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $prod): ?>
                <div class="product-card" data-product-id="<?= $prod->product_id ?>">
                    <?php if ($prod->image_url): ?>
                    <img src="<?= htmlspecialchars($prod->image_url) ?>" alt="<?= htmlspecialchars($prod->product_name) ?>" class="product-image">
                    <?php endif; ?>
                    <div class="product-name"><?= htmlspecialchars($prod->product_name) ?></div>
                    <div class="product-price">Rp <?= number_format($prod->price, 0, ',', '.') ?></div>
                    <div class="product-commission">Commission: <?= $prod->commission_rate ?>%</div>
                    <div class="product-sales">Sales: <?= number_format($prod->sales_count) ?> units</div>
                    <div class="product-gmv">GMV: Rp <?= number_format($prod->gmv, 0, ',', '.') ?></div>
                    <div class="product-actions">
                        <button class="btn-generate-link" onclick="generateAffiliateLink('<?= $prod->product_id ?>', '<?= htmlspecialchars($prod->product_name) ?>')">
                            <i class="fas fa-link"></i> Generate Link
                        </button>
                        <button class="btn-remove-product" onclick="removeProduct('<?= $prod->product_id ?>', '<?= $prod->campaign_id ?>')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">No products found for this brand</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Creators Section - Dari affiliate_orders -->
    <div class="section-card">
        <h2 class="section-title">👥 Associated Creators</h2>
        <div class="creators-grid">
            <?php if (!empty($creators)): ?>
                <?php foreach ($creators as $creator): ?>
                <div class="creator-card">
                    <div class="creator-name">
                        <i class="fas fa-user-circle"></i>
                        <?= htmlspecialchars($creator->creator_username ?? $creator->username ?? 'Unknown') ?>
                    </div>
                    <div class="creator-stats">
                        <span>💰 GMV: Rp <?= number_format($creator->total_gmv ?? 0, 0, ',', '.') ?></span>
                        <span>📦 Orders: <?= number_format($creator->total_orders ?? 0) ?></span>
                        <span>💸 Commission: Rp <?= number_format($creator->total_commission ?? 0, 0, ',', '.') ?></span>
                    </div>
                    <span class="badge-status <?= strtolower($creator->status ?? 'active') ?>">
                        <?= $creator->status ?? 'Active' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">No creators associated yet</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Orders Section -->
    <div class="section-card">
        <h2 class="section-title">🕒 Recent Orders</h2>
        <div class="orders-table">
            <?php if (!empty($recent_orders)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Creator</th>
                            <th>GMV</th>
                            <th>Commission</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order->order_id) ?></td>
                            <td><?= htmlspecialchars($order->product_name) ?></td>
                            <td>@<?= htmlspecialchars($order->creator_username) ?></td>
                            <td class="gmv-cell">Rp <?= number_format($order->gmv, 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($order->actual_commission, 0, ',', '.') ?></td>
                            <td>
                                <span class="order-status <?= strtolower($order->order_status) ?>">
                                    <?= $order->order_status ?>
                                </span>
                             </td>
                            <td><?= date('d M Y', strtotime($order->order_time)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">No orders found</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Add Product -->
<div id="addProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Product to Brand</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Product Link (Tokopedia/TikTok)</label>
                <input type="text" id="productLink" class="form-control" placeholder="Paste product link here...">
                <button class="btn-fetch" onclick="fetchProductData()">Fetch Product Data</button>
            </div>
            <div id="productPreview" style="display:none;">
                <hr>
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" id="productName" class="form-control">
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" id="productPrice" class="form-control">
                </div>
                <div class="form-group">
                    <label>Commission Rate (%)</label>
                    <input type="number" id="productCommission" class="form-control" value="10">
                </div>
                <button class="btn-submit" onclick="saveProduct()">Add to Brand</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Additional styles for brand detail */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.btn-add-product {
    background: linear-gradient(135deg, var(--purple), var(--blue));
    border: none;
    padding: 8px 16px;
    border-radius: 40px;
    color: white;
    font-size: 12px;
    cursor: pointer;
    transition: var(--transition);
}

.btn-add-product:hover {
    transform: translateY(-2px);
    box-shadow: var(--glow-purple);
}

.product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 12px;
}

.product-gmv {
    font-size: 12px;
    color: #4ade80;
    margin-top: 8px;
    font-weight: 600;
}

.product-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
}

.btn-generate-link {
    background: transparent;
    border: 1px solid var(--purple);
    color: var(--purple);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    cursor: pointer;
    transition: var(--transition);
}

.btn-generate-link:hover {
    background: var(--purple);
    color: white;
}

.btn-remove-product {
    background: transparent;
    border: 1px solid #ef4444;
    color: #ef4444;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    cursor: pointer;
    transition: var(--transition);
}

.btn-remove-product:hover {
    background: #ef4444;
    color: white;
}

.orders-table {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    text-align: left;
    padding: 12px 8px;
    color: var(--text-muted);
    font-size: 12px;
    border-bottom: 1px solid var(--border);
}

.data-table td {
    padding: 12px 8px;
    color: var(--text-primary);
    font-size: 13px;
    border-bottom: 1px solid var(--border-light);
}

.order-status {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 500;
}

.order-status.completed {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.order-status.processing {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
}

.order-status.cancelled {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
}

.modal-content {
    background: var(--bg-card);
    margin: 10% auto;
    width: 90%;
    max-width: 500px;
    border-radius: 20px;
    border: 1px solid var(--border);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}

.modal-header h3 {
    color: var(--text-primary);
    margin: 0;
}

.close {
    color: var(--text-muted);
    font-size: 28px;
    cursor: pointer;
}

.close:hover {
    color: var(--text-primary);
}

.modal-body {
    padding: 20px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    color: var(--text-secondary);
    font-size: 12px;
    margin-bottom: 6px;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: var(--text-primary);
    font-size: 13px;
}

.btn-fetch, .btn-submit {
    margin-top: 10px;
    background: var(--purple);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 40px;
    cursor: pointer;
    width: 100%;
}

.btn-fetch:hover, .btn-submit:hover {
    background: var(--purple-dark);
}

hr {
    border-color: var(--border);
    margin: 16px 0;
}
</style>

<script>
function showAddProductModal() {
    document.getElementById('addProductModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('addProductModal').style.display = 'none';
    document.getElementById('productPreview').style.display = 'none';
    document.getElementById('productLink').value = '';
}

async function fetchProductData() {
    const link = document.getElementById('productLink').value;
    if (!link) {
        alert('Please enter a product link');
        return;
    }
    
    const response = await fetch('<?= base_url("bd/fetch_product_by_link") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `link=${encodeURIComponent(link)}`
    });
    
    const data = await response.json();
    
    if (data.success) {
        document.getElementById('productName').value = data.data.name || '';
        document.getElementById('productPrice').value = data.data.price || 0;
        document.getElementById('productPreview').style.display = 'block';
    } else if (data.requires_manual) {
        document.getElementById('productName').value = data.suggested_name || '';
        document.getElementById('productPreview').style.display = 'block';
        alert(data.message);
    } else {
        alert('Failed to fetch product: ' + data.message);
    }
}

async function saveProduct() {
    const brandId = <?= $brand->id ?>;
    const productName = document.getElementById('productName').value;
    const productPrice = document.getElementById('productPrice').value;
    const productCommission = document.getElementById('productCommission').value;
    
    if (!productName) {
        alert('Product name is required');
        return;
    }
    
    const response = await fetch('<?= base_url("bd/add_brand_product") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `brand_id=${brandId}&product_name=${encodeURIComponent(productName)}&product_price=${productPrice}&commission_rate=${productCommission}`
    });
    
    const data = await response.json();
    
    if (data.success) {
        alert('Product added successfully!');
        location.reload();
    } else {
        alert('Error: ' + data.message);
    }
}

async function generateAffiliateLink(productId, productName) {
    if (confirm(`Generate affiliate link for "${productName}"?`)) {
        const response = await fetch('<?= base_url("bd/generate_affiliate_link_for_product") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&commission=10`
        });
        
        const data = await response.json();
        
        if (data.success) {
            navigator.clipboard.writeText(data.link);
            alert('Affiliate link generated and copied to clipboard!');
        } else {
            alert('Error: ' + data.message);
        }
    }
}

async function removeProduct(productId, campaignId) {
    if (confirm('Are you sure you want to remove this product from the brand?')) {
        const brandId = <?= $brand->id ?>;
        const response = await fetch('<?= base_url("bd/remove_brand_product") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `brand_id=${brandId}&product_id=${productId}&campaign_id=${campaignId}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Product removed successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('addProductModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>