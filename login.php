<?php
require_once 'functions.php';
require_once 'config.php';

// Zaten giriş yapmışsa index'e at
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Lütfen kullanıcı adı ve şifre giriniz.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Kullanıcı bilgileri doğru - aktif oturum kontrolü yap
            $currentIp = $_SERVER['REMOTE_ADDR'];
            $activeSession = checkActiveSession($pdo, $user['id'], $currentIp);

            if ($activeSession['active']) {
                // Başka bir IP'den aktif oturum var - Özel güvenlik logu kaydet
                $logDetails = sprintf(
                    'Eşzamanlı giriş denemesi engellendi. Aktif oturum IP: %s, Deneme yapılan IP: %s',
                    $activeSession['ip'],
                    $currentIp
                );
                logActivity($pdo, $user['id'], 'es_zamanli_giris_denemesi', $logDetails);

                $error = 'Bu hesap şu anda başka bir IP adresinden (' . $activeSession['ip'] . ') kullanılıyor. Aynı anda birden fazla yerden giriş yapamazsınız.';
            } else {
                // Login Başarılı - Yeni oturum oluştur
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['display_name'] = $user['display_name'];
                $_SESSION['role'] = $user['role'] ?? 'user';
                $_SESSION['login_success'] = true;

                // Oturum kaydı oluştur
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                createUserSession($pdo, $user['id'], $currentIp, $userAgent);

                logActivity($pdo, $user['id'], 'login', 'Kullanıcı giriş yaptı.');
                header('Location: index.php');
                exit;
            }
        } else {
            $error = 'Kullanıcı adı veya şifre hatalı!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="light">

<head>
    <script>
        const storedTheme = localStorage.getItem('theme');
        if (storedTheme) {
            document.documentElement.setAttribute('data-bs-theme', storedTheme);
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <title>Giriş Yap - Araklı Adliyesi PTT İrsaliye Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --body-bg: #f4f6f9;
            --watermark-opacity: 0.05;
            --bs-body-color: #212529;
        }

        [data-bs-theme="dark"] {
            --body-bg: #212529;
            --watermark-opacity: 0.08;
            --bs-body-color: #dee2e6;
        }

        [data-bs-theme="dark"] .login-card {
            background-color: #2c3035 !important;
            color: #dee2e6;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
        }

        [data-bs-theme="dark"] .form-control {
            background-color: #343a40;
            border-color: #495057;
            color: #fff;
        }

        [data-bs-theme="dark"] .form-control::placeholder {
            color: #adb5bd;
        }

        [data-bs-theme="dark"] .text-muted {
            color: #adb5bd !important;
        }

        /* SweetAlert2 Dark Mode Overrides for Login Page */
        [data-bs-theme="dark"] .swal2-popup {
            background-color: #2c3035;
            color: #dee2e6;
        }

        [data-bs-theme="dark"] .swal2-title {
            color: #dee2e6 !important;
        }

        /* Institution Title Color Handling */
        .institution-title {
            color: #004990;
        }

        [data-bs-theme="dark"] .institution-title {
            color: #4cc9f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--body-bg);
            color: var(--bs-body-color);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            background-image: url('assets/img/watermark.png');
            background-position: center;
            background-repeat: no-repeat;
            background-size: 600px;
            opacity: var(--watermark-opacity);
            pointer-events: none;
            z-index: -1;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: none;
        }


        .brand-icon {
            font-size: 3rem;
            color: #004990;
            margin-bottom: 1rem;
        }

        .btn-primary {
            background-color: #004990;
            border-color: #004990;
            padding: 0.8rem;
        }

        .btn-primary:hover {
            background-color: #003366;
            border-color: #003366;
        }

        .bubbles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            pointer-events: none;
            overflow: hidden;
        }

        .bubble {
            position: absolute;
            bottom: -100px;
            background-color: rgba(13, 110, 253, 0.1);
            border-radius: 50%;
            animation: rise 20s infinite ease-in;
        }

        .bubble:nth-child(1) {
            width: 40px;
            height: 40px;
            left: 10%;
            animation-duration: 15s;
        }

        .bubble:nth-child(2) {
            width: 20px;
            height: 20px;
            left: 20%;
            animation-duration: 25s;
            animation-delay: 2s;
        }

        .bubble:nth-child(3) {
            width: 50px;
            height: 50px;
            left: 35%;
            animation-duration: 20s;
            animation-delay: 4s;
        }

        .bubble:nth-child(4) {
            width: 80px;
            height: 80px;
            left: 50%;
            animation-duration: 30s;
            animation-delay: 0s;
        }

        .bubble:nth-child(5) {
            width: 35px;
            height: 35px;
            left: 55%;
            animation-duration: 18s;
            animation-delay: 1s;
        }

        .bubble:nth-child(6) {
            width: 45px;
            height: 45px;
            left: 65%;
            animation-duration: 22s;
            animation-delay: 3s;
        }

        .bubble:nth-child(7) {
            width: 25px;
            height: 25px;
            left: 75%;
            animation-duration: 28s;
            animation-delay: 2s;
        }

        .bubble:nth-child(8) {
            width: 80px;
            height: 80px;
            left: 80%;
            animation-duration: 24s;
            animation-delay: 5s;
        }

        .bubble:nth-child(9) {
            width: 15px;
            height: 15px;
            left: 90%;
            animation-duration: 16s;
            animation-delay: 1s;
        }

        .bubble:nth-child(10) {
            width: 50px;
            height: 50px;
            left: 95%;
            animation-duration: 26s;
            animation-delay: 4s;
        }

        @keyframes rise {
            0% {
                bottom: -100px;
                transform: translateX(0);
            }

            50% {
                transform: translateX(100px);
            }

            100% {
                bottom: 120vh;
                transform: translateX(-200px);
            }
        }

        [data-bs-theme="dark"] .bubble {
            background-color: rgba(255, 255, 255, 0.05);
        }
    </style>
</head>

<body>
    <div class="bubbles">
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
    </div>

    <!-- Theme Toggle Button -->
    <button
        class="position-absolute top-0 end-0 m-4 btn btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center"
        style="width: 50px; height: 50px;" id="themeToggleBtn" onclick="toggleTheme()">
        <i class="fas fa-moon fa-lg"></i>
    </button>

    <div class="card login-card bg-white">
        <div class="text-center">
            <img src="assets/img/watermark.png" alt="Logo" style="height: 100px; margin-bottom: 1rem;">
            <h4 class="fw-bold mb-4">
                <span
                    class="institution-title"><?php echo getSetting('institution_name') ?: 'Araklı Adliyesi'; ?></span><br>
                <span class="text-muted h6">PTT İrsaliye Sistemi</span>
            </h4>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">KULLANICI ADI</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted small fw-bold">ŞİFRE</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold rounded-3">GİRİŞ YAP</button>
        </form>
    </div>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        }

        function updateIcon(theme) {
            const btn = document.getElementById('themeToggleBtn');
            const icon = btn.querySelector('i');
            if (theme === 'dark') {
                icon.className = 'fas fa-sun fa-lg text-warning';
                btn.classList.replace('btn-light', 'btn-dark');
                btn.style.backgroundColor = '#343a40';
                btn.style.borderColor = '#495057';
            } else {
                icon.className = 'fas fa-moon fa-lg text-secondary';
                btn.classList.replace('btn-dark', 'btn-light');
                btn.style.backgroundColor = '';
                btn.style.borderColor = '';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            updateIcon(currentTheme);
        });
    </script>

</body>

</html>