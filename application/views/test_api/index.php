<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Test TikTok API' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #0a0e17; padding: 24px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #e2f0e8; margin-bottom: 24px; }
        .card { background: #111827; border-radius: 16px; padding: 20px; margin-bottom: 20px; border: 1px solid #2a3346; }
        .card h3 { color: #4ade80; margin-bottom: 16px; }
        .param-group { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
        .param-group input, .param-group select { background: #0f1420; border: 1px solid #2a3346; border-radius: 8px; padding: 10px; color: #e2e8f0; flex: 1; min-width: 200px; }
        button { background: #4ade80; color: #0a0e17; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        button:hover { background: #22c55e; }
        pre { background: #0f1420; border-radius: 12px; padding: 16px; overflow-x: auto; font-size: 12px; color: #cbd5e6; margin-top: 16px; max-height: 500px; overflow-y: auto; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
        .status-success { background: rgba(74,222,128,0.2); color: #4ade80; }
        .status-error { background: rgba(239,68,68,0.2); color: #ef4444; }
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        @media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="container">
    <h1>🧪 TikTok API Testing Tool</h1>
    
    <div class="card">
        <h3>🔑 Cipher & Config</h3>
        <div style="display: flex; gap: 16px; flex-wrap: wrap;">
            <div><span style="color:#9aaebe;">Cipher:</span> <strong style="color:#4ade80;">ROW_fyGlKwAAAAB6jCmj_Z8Zc6uknZJUdZAi</strong></div>
            <div><span style="color:#9aaebe;">App Key:</span> <strong style="color:#e2f0e8;">6jo4rjnr8ouc9</strong></div>
            <div><span style="color:#9aaebe;">Service ID:</span> <strong style="color:#e2f0e8;">7630671107655157524</strong></div>
        </div>
    </div>

    <div class="grid-2">
        <!-- Token Status -->
        <div class="card">
            <h3>🔐 Token Status</h3>
            <button onclick="testTokenStatus()">Check Token</button>
            <pre id="tokenResult">Click button to check...</pre>
        </div>

        <!-- Get Campaigns -->
        <div class="card">
            <h3>📊 Get Campaigns</h3>
            <button onclick="testCampaigns()">Get Campaigns</button>
            <pre id="campaignsResult">Click button to test...</pre>
        </div>

        <!-- Get Products by Campaign -->
        <div class="card">
            <h3>📦 Get Products</h3>
            <div class="param-group">
                <input type="text" id="campaignId" placeholder="Campaign ID">
            </div>
            <button onclick="testProducts()">Get Products</button>
            <pre id="productsResult">Enter Campaign ID and click...</pre>
        </div>

        <!-- Search Products -->
        <div class="card">
            <h3>🔍 Search Products</h3>
            <div class="param-group">
                <input type="text" id="searchKeyword" placeholder="Keyword (e.g., skincare)">
                <select id="searchCategory">
                    <option value="">All Categories</option>
                    <option value="BEAUTY">Beauty</option>
                    <option value="ELECTRONICS">Electronics</option>
                    <option value="FASHION">Fashion</option>
                </select>
            </div>
            <button onclick="testSearchProducts()">Search</button>
            <pre id="searchResult">Enter keyword and click...</pre>
        </div>

        <!-- Get Orders -->
        <div class="card">
            <h3>📋 Get Orders</h3>
            <div class="param-group">
                <input type="text" id="orderCampaignId" placeholder="Campaign ID (optional)">
                <input type="number" id="orderDays" placeholder="Days (default 30)" value="30">
            </div>
            <button onclick="testOrders()">Get Orders</button>
            <pre id="ordersResult">Click button to test...</pre>
        </div>

        <!-- Search Creators -->
        <div class="card">
            <h3>👥 Search Creators</h3>
            <div class="param-group">
                <input type="text" id="creatorKeyword" placeholder="Keyword">
                <select id="creatorCategory">
                    <option value="">All Categories</option>
                    <option value="BEAUTY">Beauty</option>
                    <option value="FASHION">Fashion</option>
                </select>
            </div>
            <button onclick="testCreators()">Search Creators</button>
            <pre id="creatorsResult">Enter keyword and click...</pre>
        </div>

        <!-- Generate Affiliate Link -->
        <div class="card">
            <h3>🔗 Generate Affiliate Link</h3>
            <div class="param-group">
                <input type="text" id="linkCampaignId" placeholder="Campaign ID">
                <input type="text" id="linkProductId" placeholder="Product ID">
                <input type="number" id="linkCommission" placeholder="Commission %" value="10">
            </div>
            <button onclick="testGenerateLink()">Generate Link</button>
            <pre id="linkResult">Enter Campaign ID, Product ID and click...</pre>
        </div>

        <!-- Test All -->
        <div class="card">
            <h3>🚀 Test All Endpoints</h3>
            <button onclick="testAll()">Run All Tests</button>
            <pre id="allResult">Click button to test all...</pre>
        </div>
    </div>
</div>

<script>
    const baseUrl = '<?= base_url() ?>';

    async function apiCall(endpoint, params = {}) {
        const url = new URL(baseUrl + 'index.php/test_api/' + endpoint);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        const response = await fetch(url);
        return await response.json();
    }

    async function testTokenStatus() {
        document.getElementById('tokenResult').innerText = 'Loading...';
        const result = await apiCall('token_status');
        document.getElementById('tokenResult').innerText = JSON.stringify(result, null, 2);
    }

    async function testCampaigns() {
        document.getElementById('campaignsResult').innerText = 'Loading...';
        const result = await apiCall('get_campaigns');
        document.getElementById('campaignsResult').innerText = JSON.stringify(result, null, 2);
    }

    async function testProducts() {
        const campaignId = document.getElementById('campaignId').value;
        if (!campaignId) {
            document.getElementById('productsResult').innerText = 'Please enter Campaign ID';
            return;
        }
        document.getElementById('productsResult').innerText = 'Loading...';
        const result = await apiCall('get_products', { campaign_id: campaignId });
        document.getElementById('productsResult').innerText = JSON.stringify(result, null, 2);
    }

    async function testSearchProducts() {
        const keyword = document.getElementById('searchKeyword').value;
        if (!keyword) {
            document.getElementById('searchResult').innerText = 'Please enter keyword';
            return;
        }
        const category = document.getElementById('searchCategory').value;
        document.getElementById('searchResult').innerText = 'Loading...';
        const params = { keyword: keyword };
        if (category) params.category = category;
        const result = await apiCall('search_products', params);
        document.getElementById('searchResult').innerText = JSON.stringify(result, null, 2);
    }

    async function testOrders() {
        const campaignId = document.getElementById('orderCampaignId').value;
        const days = document.getElementById('orderDays').value;
        document.getElementById('ordersResult').innerText = 'Loading...';
        const params = {};
        if (campaignId) params.campaign_id = campaignId;
        if (days) params.days = days;
        const result = await apiCall('get_orders', params);
        document.getElementById('ordersResult').innerText = JSON.stringify(result, null, 2);
    }

    async function testCreators() {
        const keyword = document.getElementById('creatorKeyword').value;
        const category = document.getElementById('creatorCategory').value;
        document.getElementById('creatorsResult').innerText = 'Loading...';
        const params = {};
        if (keyword) params.keyword = keyword;
        if (category) params.category = category;
        const result = await apiCall('get_creators', params);
        document.getElementById('creatorsResult').innerText = JSON.stringify(result, null, 2);
    }

    async function testGenerateLink() {
        const campaignId = document.getElementById('linkCampaignId').value;
        const productId = document.getElementById('linkProductId').value;
        const commission = document.getElementById('linkCommission').value;
        
        if (!campaignId || !productId) {
            document.getElementById('linkResult').innerText = 'Please enter Campaign ID and Product ID';
            return;
        }
        
        document.getElementById('linkResult').innerText = 'Loading...';
        const result = await apiCall('generate_link', { 
            campaign_id: campaignId, 
            product_id: productId, 
            commission: commission 
        });
        document.getElementById('linkResult').innerText = JSON.stringify(result, null, 2);
    }

    async function testAll() {
        document.getElementById('allResult').innerText = 'Running all tests...';
        const result = await apiCall('test_all');
        document.getElementById('allResult').innerText = JSON.stringify(result, null, 2);
    }
</script>
</body>
</html>