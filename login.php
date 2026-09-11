<?php
session_start();

// Nếu đã đăng nhập rồi thì chuyển hướng về trang chủ
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Danh sách tài khoản mẫu để test
$valid_users = [
    'admin' => '123456',
    'developer' => 'cicd@2026'
];

// Xử lý khi submit Form (PHP thuần)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!';
    } elseif (!isset($valid_users[$username]) || $valid_users[$username] !== $password) {
        $error = 'Tên đăng nhập hoặc mật khẩu không chính xác!';
    } else {
        // Đăng nhập thành công -> Lưu session
        $_SESSION['user'] = $username;
        $_SESSION['login_time'] = date('Y-m-d H:i:s');
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Hệ Thống - CI/CD Test</title>
    <meta name="description" content="Trang đăng nhập PHP thuần có validate Javascript kiểm tra CI/CD">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b0f19;
            --card-bg: rgba(18, 24, 43, 0.85);
            --border: rgba(255, 255, 255, 0.08);
            --border-focus: #38bdf8;
            --primary: #38bdf8;
            --primary-hover: #0ea5e9;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --danger: #f43f5e;
            --danger-bg: rgba(244, 63, 94, 0.12);
            --success: #10b981;
            --success-bg: rgba(16, 185, 129, 0.12);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(56, 189, 248, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.15) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
            color: var(--text-main);
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #38bdf8, #818cf8, #c084fc);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(129, 140, 248, 0.2));
            border: 1px solid rgba(56, 189, 248, 0.3);
            margin-bottom: 16px;
            color: var(--primary);
            font-size: 24px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .header p {
            color: var(--text-muted);
            font-size: 14px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            line-height: 1.4;
        }

        .alert-error {
            background: var(--danger-bg);
            border: 1px solid rgba(244, 63, 94, 0.3);
            color: #fda4af;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #cbd5e1;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 13px 16px;
            font-size: 15px;
            color: var(--text-main);
            outline: none;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15);
            background: rgba(15, 23, 42, 0.95);
        }

        .form-input.is-invalid {
            border-color: var(--danger);
            box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.15);
        }

        .error-feedback {
            color: var(--danger);
            font-size: 12.5px;
            margin-top: 6px;
            display: none;
            font-weight: 500;
        }

        .error-feedback.visible {
            display: block;
        }

        .btn-toggle-pwd {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            font-size: 14px;
        }

        .btn-toggle-pwd:hover {
            color: var(--text-main);
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            color: #0f172a;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -5px rgba(56, 189, 248, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .demo-credentials {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px dashed var(--border);
            font-size: 13px;
            color: var(--text-muted);
        }

        .demo-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-weight: 600;
            color: #cbd5e1;
        }

        .credential-box {
            background: rgba(15, 23, 42, 0.6);
            border-radius: 8px;
            padding: 10px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: monospace;
            border: 1px solid var(--border);
        }

        .btn-autofill {
            background: rgba(56, 189, 248, 0.15);
            border: 1px solid rgba(56, 189, 248, 0.3);
            color: var(--primary);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-autofill:hover {
            background: rgba(56, 189, 248, 0.25);
        }

        .footer-tag {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }

        /* Shake animation khi validate lỗi */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        .shake {
            animation: shake 0.4s ease-in-out;
        }
    </style>
</head>
<body>

    <div class="login-card" id="loginCard">
        <div class="header">
            <div class="logo-badge">⚡</div>
            <h1>Đăng Nhập Hệ Thống</h1>
            <p>Trang thử nghiệm quy trình CI/CD tự động</p>
        </div>

        <!-- Thông báo lỗi từ PHP server (nếu có) -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span>⚠️</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Form đăng nhập -->
        <form id="loginForm" method="POST" action="login.php" novalidate>
            <!-- Tên đăng nhập -->
            <div class="form-group">
                <label class="form-label" for="username">Tên đăng nhập / Tài khoản</label>
                <div class="input-wrapper">
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        placeholder="Nhập tên đăng nhập..."
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        autocomplete="username"
                    >
                </div>
                <div class="error-feedback" id="usernameError">Vui lòng nhập tên đăng nhập (tối thiểu 3 ký tự).</div>
            </div>

            <!-- Mật khẩu -->
            <div class="form-group">
                <label class="form-label" for="password">Mật khẩu</label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Nhập mật khẩu..."
                        autocomplete="current-password"
                    >
                    <button type="button" class="btn-toggle-pwd" id="togglePassword" aria-label="Ẩn hiện mật khẩu">👁️</button>
                </div>
                <div class="error-feedback" id="passwordError">Mật khẩu không được để trống (tối thiểu 6 ký tự).</div>
            </div>

            <!-- Nút đăng nhập -->
            <button type="submit" class="btn-submit" id="submitBtn">
                <span>Đăng Nhập</span>
                <span>→</span>
            </button>
        </form>

        <!-- Khối tài khoản test mẫu -->
        <div class="demo-credentials">
            <div class="demo-title">
                <span>Tài khoản mẫu để test:</span>
                <button type="button" class="btn-autofill" id="btnAutoFill">Tự điền nhanh</button>
            </div>
            <div class="credential-box">
                <span>User: <strong>admin</strong></span>
                <span>Pass: <strong>123456</strong></span>
            </div>
        </div>

        <div class="footer-tag">
            CI/CD Deployment Test &bull; PHP <?php echo phpversion(); ?>
        </div>
    </div>

    <!-- Javascript Client-side Validation -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('loginForm');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const usernameError = document.getElementById('usernameError');
            const passwordError = document.getElementById('passwordError');
            const togglePasswordBtn = document.getElementById('togglePassword');
            const btnAutoFill = document.getElementById('btnAutoFill');
            const loginCard = document.getElementById('loginCard');

            // 1. Chức năng ẩn/hiện mật khẩu
            togglePasswordBtn.addEventListener('click', () => {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                togglePasswordBtn.textContent = isPassword ? '🔒' : '👁️';
            });

            // 2. Tự điền nhanh tài khoản test
            btnAutoFill.addEventListener('click', () => {
                usernameInput.value = 'admin';
                passwordInput.value = '123456';
                clearError(usernameInput, usernameError);
                clearError(passwordInput, passwordError);
                usernameInput.focus();
            });

            // 3. Hàm hiển thị và xóa lỗi
            function showError(input, errorElement, message) {
                input.classList.add('is-invalid');
                errorElement.textContent = message;
                errorElement.classList.add('visible');
            }

            function clearError(input, errorElement) {
                input.classList.remove('is-invalid');
                errorElement.classList.remove('visible');
            }

            // Xóa lỗi khi người dùng đang gõ phím
            usernameInput.addEventListener('input', () => {
                if (usernameInput.value.trim().length >= 3) {
                    clearError(usernameInput, usernameError);
                }
            });

            passwordInput.addEventListener('input', () => {
                if (passwordInput.value.trim().length >= 6) {
                    clearError(passwordInput, passwordError);
                }
            });

            // 4. Validate Form khi Submit
            form.addEventListener('submit', (e) => {
                let isValid = true;
                const usernameVal = usernameInput.value.trim();
                const passwordVal = passwordInput.value.trim();

                // Kiểm tra tên đăng nhập
                if (usernameVal === '') {
                    showError(usernameInput, usernameError, 'Vui lòng không để trống tên đăng nhập.');
                    isValid = false;
                } else if (usernameVal.length < 3) {
                    showError(usernameInput, usernameError, 'Tên đăng nhập phải có ít nhất 3 ký tự.');
                    isValid = false;
                } else {
                    clearError(usernameInput, usernameError);
                }

                // Kiểm tra mật khẩu
                if (passwordVal === '') {
                    showError(passwordInput, passwordError, 'Vui lòng không để trống mật khẩu.');
                    isValid = false;
                } else if (passwordVal.length < 6) {
                    showError(passwordInput, passwordError, 'Mật khẩu phải có ít nhất 6 ký tự.');
                    isValid = false;
                } else {
                    clearError(passwordInput, passwordError);
                }

                // Nếu có lỗi -> Ngăn form submit và rung card cảnh báo
                if (!isValid) {
                    e.preventDefault();
                    loginCard.classList.remove('shake');
                    void loginCard.offsetWidth; // Trigger reflow để kích hoạt lại animation
                    loginCard.classList.add('shake');
                }
            });
        });
    </script>
</body>
</html>
