<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Palette Tidak Ditemukan - Toopai</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #070a13;
            --card-bg: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.08);
            --text: #e2f0e8;
            --text-muted: #9aaebe;
            --purple: #8b5cf6;
            --cyan: #06b6d4;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'Outfit', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 450px;
            width: 100%;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 40px 24px;
            text-align: center;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: #ef4444;
            font-size: 32px;
        }

        h2 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #ef4444, #f59e0b);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        p {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn-home {
            display: inline-block;
            background: linear-gradient(135deg, var(--purple), var(--cyan));
            color: white;
            padding: 12px 30px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-wrapper">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2>Link Kadaluarsa atau Tidak Valid</h2>
        <p>Maaf, Product Palette Link yang Anda tuju tidak ditemukan, sudah dinonaktifkan, atau telah kadaluarsa. Silakan hubungi tim kami untuk mendapatkan link terbaru.</p>
        <a href="https://toopai.ai" class="btn-home"><i class="fas fa-home"></i> Kembali ke Toopai</a>
    </div>
</body>
</html>
