<!-- file: application/views/creator/complete_registration.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Complete Registration - Toopai</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, 'SF Pro Text', Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #0a0e1a 0%, #0f1420 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-container {
            background: #111827;
            border-radius: 28px;
            border: 1px solid #2a3346;
            width: 100%;
            max-width: 500px;
            padding: 32px;
        }
        .tiktok-profile {
            display: flex;
            align-items: center;
            gap: 16px;
            background: rgba(6, 182, 212, 0.1);
            border-radius: 20px;
            padding: 16px;
            margin-bottom: 24px;
            border: 1px solid rgba(6, 182, 212, 0.3);
        }
        .tiktok-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .tiktok-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .tiktok-info h4 {
            color: #e2f0e8;
            margin-bottom: 4px;
        }
        .tiktok-info p {
            color: #9aaebe;
            font-size: 12px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            color: #bdf2c0;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 6px;
        }
        input, select {
            width: 100%;
            padding: 12px 14px;
            background: #0f1420;
            border: 1px solid #2a3346;
            border-radius: 14px;
            color: #e2f0e8;
            font-size: 14px;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #8b5cf6;
        }
        .btn-register {
            width: 100%;
            background: linear-gradient(135deg, #8b5cf6, #3b82f6);
            border: none;
            padding: 12px;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            margin-top: 8px;
        }
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            padding: 10px;
            border-radius: 12px;
            color: #ef4444;
            font-size: 12px;
            margin-bottom: 16px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="tiktok-profile">
            <div class="tiktok-avatar">
                <?php if (!empty($tiktok_avatar)): ?>
                    <img src="<?= $tiktok_avatar ?>" alt="TikTok Avatar">
                <?php else: ?>
                    <i class="fab fa-tiktok" style="font-size: 30px; color: #06b6d4;"></i>
                <?php endif; ?>
            </div>
            <div class="tiktok-info">
                <h4><?= htmlspecialchars($tiktok_display_name) ?></h4>
                <p><i class="fab fa-tiktok"></i> Verified TikTok Account</p>
            </div>
        </div>
        
        <div class="title">Complete Your Profile</div>
        <div class="subtitle">Tell us more about yourself</div>
        
        <div id="errorMessage" class="error-message"></div>
        
        <form id="registerForm">
            <div class="form-group">
                <label>Username *</label>
                <input type="text" id="username" value="<?= htmlspecialchars($suggested_username) ?>" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" id="email" value="<?= htmlspecialchars($suggested_email) ?>" required>
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="full_name" value="<?= htmlspecialchars($tiktok_display_name) ?>">
            </div>
            <div class="form-group">
                <label>WhatsApp Number</label>
                <input type="tel" id="phone" placeholder="+62 812 3456 7890">
            </div>
            <div class="form-group">
                <label>Category</label>
                <select id="category">
                    <option value="Beauty">💄 Beauty</option>
                    <option value="Fashion">👗 Fashion</option>
                    <option value="Tech">📱 Technology</option>
                    <option value="Lifestyle">🌟 Lifestyle</option>
                    <option value="Gaming">🎮 Gaming</option>
                    <option value="Food">🍔 Food & Beverages</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password (Optional)</label>
                <input type="password" id="password" placeholder="Set password for email login">
            </div>
            <button type="submit" class="btn-register">Complete Registration</button>
        </form>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            
            if (!username || !email) {
                showError('Username and email are required');
                return;
            }
            
            try {
                const response = await fetch('<?= base_url("creator_auth/do_complete_registration") ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        username: username,
                        email: email,
                        full_name: document.getElementById('full_name').value,
                        phone: document.getElementById('phone').value,
                        category: document.getElementById('category').value,
                        password: document.getElementById('password').value
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    showError(data.message);
                }
            } catch (error) {
                showError('Network error: ' + error.message);
            }
        });
        
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 4000);
        }
    </script>
</body>
</html>