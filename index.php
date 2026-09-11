<?php
session_start();
$isLoggedIn = isset($_SESSION['user']);
$username = $isLoggedIn ? $_SESSION['user'] : null;
$loginTime = $isLoggedIn ? ($_SESSION['login_time'] ?? 'Vừa xong') : null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Test CI/CD PHP Deployment</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(18, 24, 43, 0.85);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #38bdf8;
            --primary-hover: #0ea5e9;
            --success: #10b981;
            --border: rgba(255, 255, 255, 0.08);
            --danger: #f43f5e;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.12) 0px, transparent 50%);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 24px;
        }

        .card {
            background-color: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 36px;
            max-width: 560px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #38bdf8, #818cf8, #c084fc);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge .dot {
            width: 8px;
            height: 8px;
            background-color: var(--success);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 10px var(--success);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 12px;
            color: var(--primary);
            letter-spacing: -0.5px;
        }

        p.desc {
            color: var(--text-muted);
            font-size: 14.5px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .user-status-box {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-status-info {
            text-align: left;
        }

        .user-status-info strong {
            color: #38bdf8;
            font-size: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: 0.2s;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            color: #0f172a;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);
        }

        .btn-logout {
            background: rgba(244, 63, 94, 0.15);
            color: #fda4af;
            border: 1px solid rgba(244, 63, 94, 0.3);
        }

        .btn-logout:hover {
            background: rgba(244, 63, 94, 0.25);
        }

        .info-grid {
            background: rgba(15, 23, 42, 0.7);
            border-radius: 12px;
            padding: 18px;
            text-align: left;
            font-size: 13.5px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            border: 1px solid var(--border);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed var(--border);
            padding-bottom: 8px;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .label {
            color: var(--text-muted);
        }

        .value {
            font-weight: 600;
            color: var(--text-main);
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">
            <span class="dot"></span>
            CI/CD Deployment Hoạt Động
        </div>
        <h1>Test CI/CD PHP update lần số 1</h1>
        <p class="desc">Trang web được tự động deploy qua GitHub Actions &amp; SSH lên VPS.</p>
        
        <!-- Khối trạng thái đăng nhập -->
        <div class="user-status-box">
            <?php if ($isLoggedIn): ?>
                <div class="user-status-info">
                    <p style="font-size: 13px; color: var(--text-muted);">Đã đăng nhập với tư cách:</p>
                    <strong>👤 <?php echo htmlspecialchars($username); ?></strong>
                </div>
                <a href="logout.php" class="btn btn-logout">Đăng Xuất</a>
            <?php else: ?>
                <div class="user-status-info">
                    <p style="font-size: 13px; color: var(--text-muted);">Trạng thái:</p>
                    <span style="color: #cbd5e1; font-weight: 500;">Chưa đăng nhập</span>
                </div>
                <a href="login.php" class="btn btn-primary">Đăng Nhập ➔</a>
            <?php endif; ?>
        </div>

        <div class="info-grid">
            <div class="info-row">
                <span class="label">Phiên bản PHP:</span>
                <span class="value"><?php echo phpversion(); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Máy chủ Web:</span>
                <span class="value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'PHP Development Server'; ?></span>
            </div>
            <div class="info-row">
                <span class="label">Host / IP:</span>
                <span class="value"><?php echo $_SERVER['SERVER_ADDR'] ?? $_SERVER['HTTP_HOST'] ?? 'Local'; ?></span>
            </div>
            <div class="info-row">
                <span class="label">Thời gian máy chủ:</span>
                <span class="value"><?php echo date('Y-m-d H:i:s'); ?></span>
            </div>
            <div class="info-row">
                <span class="label">Phiên bản code:</span>
                <span class="value">v1.1.0-login update code lần 1</span>
            </div>
        </div>
    </div>
</body>
</html>
