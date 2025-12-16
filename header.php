<?php
require_once 'functions.php';
require_once 'config.php';

// Eğer login sayfası değilse ve kullanıcı giriş yapmamışsa, login'e at
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login.php');
    exit;
}

// Kullanıcı giriş yapmışsa oturum geçerliliğini kontrol et
if (isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    if (!validateUserSession($pdo)) {
        // Oturum geçersiz - login sayfasına yönlendir
        header('Location: login.php');
        exit;
    }
}

$currentUser = isset($_SESSION['display_name']) ? formatUnitName($_SESSION['display_name']) : '';
$institutionName = getSetting('institution_name') ?: 'Araklı Adliyesi';
?>
<!DOCTYPE html>
<html lang="tr" data-bs-theme="light">

<head>
    <script>
        // Check local storage for theme preference
        const storedTheme = localStorage.getItem('theme');
        if (storedTheme) {
            document.documentElement.setAttribute('data-bs-theme', storedTheme);
        } else {
            // Default to light or check system preference
            // document.documentElement.setAttribute('data-bs-theme', 'light');
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <title><?php echo escape($institutionName); ?> PTT İrsaliye Sistemi</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- JsBarcode -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/tr.js"></script>
    <style>
        :root {
            --body-bg: #f4f6f9;
            --watermark-opacity: 0.04;
        }

        [data-bs-theme="dark"] {
            --body-bg: #212529;
            --watermark-opacity: 0.08;
            /* Make it slightly more visible on dark */
            --bs-body-color: #dee2e6;
            --bs-body-bg: #212529;
        }

        [data-bs-theme="dark"] .card {
            background-color: #2c3035;
            color: #dee2e6;
            box-shadow: 0 4px 6px rgba(0, 0, 0, .4) !important;
        }

        [data-bs-theme="dark"] .card-header {
            background-color: #343a40 !important;
            color: #fff;
            border-bottom-color: #495057;
        }

        [data-bs-theme="dark"] .text-muted {
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #343a40;
            border-color: #495057;
            color: #fff;
        }

        [data-bs-theme="dark"] .form-control::placeholder {
            color: #adb5bd;
        }

        [data-bs-theme="dark"] .input-group-text {
            background-color: #343a40;
            border-color: #495057;
            color: #adb5bd;
        }

        [data-bs-theme="dark"] .table {
            color: #dee2e6;
            border-color: #495057;
        }

        [data-bs-theme="dark"] .modal-content {
            background-color: #2c3035;
            color: #dee2e6;
        }

        [data-bs-theme="dark"] .modal-header,
        [data-bs-theme="dark"] .modal-footer {
            background-color: #343a40 !important;
            border-color: #495057;
        }

        [data-bs-theme="dark"] .bg-white {
            background-color: #2c3035 !important;
            color: #dee2e6 !important;
        }

        [data-bs-theme="dark"] .bg-light {
            background-color: #343a40 !important;
            color: #dee2e6 !important;
        }

        /* SweetAlert2 Dark Mode Overrides */
        [data-bs-theme="dark"] .swal2-popup {
            background-color: #2c3035;
            color: #dee2e6;
        }

        [data-bs-theme="dark"] .swal2-title,
        [data-bs-theme="dark"] .swal2-content,
        [data-bs-theme="dark"] .swal2-html-container {
            color: #dee2e6 !important;
        }

        [data-bs-theme="dark"] .swal2-input,
        [data-bs-theme="dark"] .swal2-textarea,
        [data-bs-theme="dark"] .swal2-validation-message {
            background-color: #343a40;
            color: #fff;
            border-color: #495057;
        }

        [data-bs-theme="dark"] .swal2-input:focus {
            box-shadow: 0 0 0 1px #0d6efd;
        }

        /* Fix inputs inside SweetAlert2 html content which might use bootstrap form-control */
        [data-bs-theme="dark"] .swal2-html-container .form-control {
            background-color: #343a40 !important;
            color: #fff !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] .swal2-html-container .form-label {
            color: #adb5bd !important;
        }

        /* Flatpickr Dark Mode Overrides */
        [data-bs-theme="dark"] .flatpickr-calendar {
            background: #2c3035 !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        [data-bs-theme="dark"] .flatpickr-innerContainer,
        [data-bs-theme="dark"] .flatpickr-rContainer,
        [data-bs-theme="dark"] .flatpickr-days,
        [data-bs-theme="dark"] .dayContainer {
            background: #2c3035 !important;
        }

        [data-bs-theme="dark"] .flatpickr-day {
            background: transparent;
            color: #e0e0e0 !important;
            border-color: transparent;
        }

        [data-bs-theme="dark"] .flatpickr-day.flatpickr-disabled,
        [data-bs-theme="dark"] .flatpickr-day.flatpickr-disabled:hover {
            color: #6c757d !important;
            background: transparent !important;
        }

        [data-bs-theme="dark"] .flatpickr-day.prevMonthDay,
        [data-bs-theme="dark"] .flatpickr-day.nextMonthDay {
            color: #6c757d !important;
            background: transparent !important;
        }

        [data-bs-theme="dark"] .flatpickr-day:hover,
        [data-bs-theme="dark"] .flatpickr-day:focus {
            background: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
        }

        [data-bs-theme="dark"] .flatpickr-day.selected {
            background: #0d6efd !important;
            color: #fff !important;
            border-color: #0d6efd !important;
        }

        [data-bs-theme="dark"] .flatpickr-day.today {
            background: transparent !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }

        [data-bs-theme="dark"] .flatpickr-day.today:hover {
            background: #e9ecef !important;
            border-color: #e9ecef !important;
            color: #212529 !important;
        }

        /* Month/Year Header */
        [data-bs-theme="dark"] .flatpickr-month,
        [data-bs-theme="dark"] .flatpickr-weekdays {
            background: #2c3035 !important;
            color: #fff !important;
        }

        [data-bs-theme="dark"] span.flatpickr-weekday {
            background: #2c3035 !important;
            color: #adb5bd !important;
        }

        /* Dropdowns (Month/Year) */
        [data-bs-theme="dark"] .flatpickr-current-month .flatpickr-monthDropdown-months {
            background-color: #2c3035;
            color: #fff;
        }

        [data-bs-theme="dark"] .flatpickr-current-month .flatpickr-monthDropdown-months:hover {
            background-color: #343a40;
        }

        [data-bs-theme="dark"] .flatpickr-current-month input.cur-year {
            background-color: transparent;
            color: #fff;
        }

        /* Arrows */
        [data-bs-theme="dark"] .flatpickr-months .flatpickr-prev-month,
        [data-bs-theme="dark"] .flatpickr-months .flatpickr-next-month {
            fill: #fff !important;
            color: #fff !important;
        }

        [data-bs-theme="dark"] .flatpickr-months .flatpickr-prev-month:hover svg,
        [data-bs-theme="dark"] .flatpickr-months .flatpickr-next-month:hover svg {
            fill: #adb5bd !important;
        }

        /* Table Header Dark Mode */
        [data-bs-theme="dark"] .table-light th,
        [data-bs-theme="dark"] .table-light td,
        [data-bs-theme="dark"] .table-light {
            background-color: #343a40;
            color: #dee2e6;
            border-color: #495057;
        }

        /* Navbar Styling */
        .navbar-custom {
            background-color: #004990;
            /* Light mode: Brand Blue */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }

        .institution-name {
            color: #fff;
            /* Light mode: White text on blue bg */
            font-weight: 700;
        }

        [data-bs-theme="dark"] .navbar-custom {
            background-color: #0b0d0f !important;
            /* Dark mode: Deep Dark */
            border-bottom: 1px solid #2c3035;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }

        [data-bs-theme="dark"] .institution-name {
            color: #4cc9f0;
            /* Dark mode: Cyan text on dark bg */
        }

        /* Stat Cards Styling */
        .stat-card {
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        /* Light Mode Defaults (Solid Colors) */
        .stat-card-primary {
            background-color: #0d6efd;
            color: #fff;
        }

        .stat-card-primary .stat-title {
            color: rgba(255, 255, 255, 0.8);
        }

        .stat-card-success {
            background-color: #198754;
            color: #fff;
        }

        .stat-card-success .stat-title {
            color: rgba(255, 255, 255, 0.8);
        }

        .stat-card-info {
            background-color: #0dcaf0;
            color: #fff;
        }

        .stat-card-info .stat-title {
            color: rgba(255, 255, 255, 0.8);
        }

        .stat-card-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .stat-card-warning .stat-title {
            color: rgba(33, 37, 41, 0.8);
        }

        /* Dark Mode Overrides (Dark BG with Colored accents) */
        [data-bs-theme="dark"] .stat-card-primary {
            background-color: #051b3b !important;
            border: 1px solid #084298;
            color: #6ea8fe;
        }

        [data-bs-theme="dark"] .stat-card-primary .stat-title {
            color: #9ec5fe;
        }

        [data-bs-theme="dark"] .stat-card-success {
            background-color: #051b11 !important;
            border: 1px solid #0f5132;
            color: #75b798;
        }

        [data-bs-theme="dark"] .stat-card-success .stat-title {
            color: #a3cfbb;
        }

        [data-bs-theme="dark"] .stat-card-info {
            background-color: #032830 !important;
            border: 1px solid #087990;
            color: #6edff6;
        }

        [data-bs-theme="dark"] .stat-card-info .stat-title {
            color: #9eeaf9;
        }

        [data-bs-theme="dark"] .stat-card-warning {
            background-color: #332701 !important;
            border: 1px solid #997404;
            color: #ffda6a;
        }

        [data-bs-theme="dark"] .stat-card-warning .stat-title {
            color: #ffe69c;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--body-bg);
            position: relative;
            min-height: 100vh;
            color: var(--bs-body-color);
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
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

        /* Adjust card shadow for dark mode */
        [data-bs-theme="dark"] .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, .2) !important;
            background-color: #2c3035;
        }

        /* Dark mode table adjustments */
        [data-bs-theme="dark"] .table {
            color: #dee2e6;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, .08);
        }

        .navbar-custom {
            position: relative;
            /* overflow: hidden; REMOVED to allow dropdowns */
            background-color: #004990;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1030; /* Bootstrap navbar default */
        }

        /* Nav content above fog */
        .navbar-custom .container {
            position: relative;
            z-index: 5;
        }

        /* Fog Animation Layer */
        .navbar-custom::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg,
                    rgba(255, 255, 255, 0) 0%,
                    rgba(255, 255, 255, 0.1) 25%,
                    rgba(255, 255, 255, 0.2) 50%,
                    rgba(255, 255, 255, 0.1) 75%,
                    rgba(255, 255, 255, 0) 100%);
            background-size: 200% 100%;
            filter: blur(10px);
            animation: fogFlow 15s linear infinite;
            z-index: 1;
            pointer-events: none;
        }

        @keyframes fogFlow {
            0% {
                background-position: 100% 0;
            }

            100% {
                background-position: -100% 0;
            }
        }

        [data-bs-theme="dark"] .navbar-custom {
            background-color: #212529;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        [data-bs-theme="dark"] .navbar-custom::before {
            background: linear-gradient(90deg,
                    rgba(255, 255, 255, 0) 0%,
                    rgba(255, 255, 255, 0.05) 50%,
                    rgba(255, 255, 255, 0) 100%);
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, .04);
        }

        .btn-primary {
            background-color: #004990;
            border-color: #004990;
        }

        .btn-primary:hover {
            background-color: #003366;
            border-color: #003366;
        }

        /* Dark Mode Button Overrides */
        [data-bs-theme="dark"] .btn-outline-dark {
            color: #dee2e6;
            border-color: #dee2e6;
        }

        [data-bs-theme="dark"] .btn-outline-dark:hover {
            background-color: #dee2e6;
            color: #212529;
        }

        [data-bs-theme="dark"] .btn-dark {
            background-color: #343a40;
            border-color: #495057;
            color: #fff;
        }

        [data-bs-theme="dark"] .btn-dark:hover {
            background-color: #495057;
            border-color: #6c757d;
        }

        [data-bs-theme="dark"] .btn-outline-primary {
            color: #6ea8fe;
            border-color: #6ea8fe;
        }

        [data-bs-theme="dark"] .btn-outline-primary:hover {
            background-color: #6ea8fe;
            color: #000;
        }

        /* Dropdown Menu Dark Mode Styles */
        [data-bs-theme="dark"] .dropdown-menu {
            background-color: #2c3035;
            border-color: #495057;
        }

        [data-bs-theme="dark"] .dropdown-item {
            color: #dee2e6;
        }

        [data-bs-theme="dark"] .dropdown-item:hover,
        [data-bs-theme="dark"] .dropdown-item:focus {
            background-color: #343a40;
            color: #fff;
        }

        [data-bs-theme="dark"] .dropdown-divider {
            border-color: #495057;
        }

        [data-bs-theme="dark"] .dropdown-header {
            color: #adb5bd;
        }

        .barcode-font {
            font-family: 'Libre Barcode 39', cursive;
            font-size: 48px;
            line-height: 1;
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

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
                <img src="assets/img/watermark.png" alt="Logo" style="height: 40px;" class="me-2">
                <span>
                    <span class="institution-name"><?php echo escape($institutionName); ?></span>
                    <span class="text-white-50 ms-1" style="font-weight: 400;">PTT İrsaliye Sistemi</span>
                </span>
            </a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="d-flex align-items-center">

                    <span id="headerClock" class="text-white-50 small me-3 fw-bold d-none d-lg-inline"
                        style="font-size: 0.85rem;"></span>

                    <!-- Theme Toggle -->
                    <button class="btn btn-link nav-link text-white me-3 p-0" onclick="toggleTheme()"
                        title="Temayı Değiştir">
                        <i id="themeIcon" class="fas fa-moon" style="font-size: 1.2rem;"></i>
                    </button>

                    <div class="vr h-50 mx-2 bg-secondary d-none d-lg-block"></div>

                    <!-- Anasayfa Button -->
                    <a href="index.php" class="btn btn-sm btn-success text-white me-2 fw-bold ms-2">
                        <i class="fas fa-home me-1"></i> Anasayfa
                    </a>

                    <!-- Sorgula Button -->
                    <a href="search.php" class="btn btn-sm btn-light text-dark me-2 fw-bold">
                        <i class="fas fa-search me-1 text-info"></i> Sorgula
                    </a>

                    <!-- Admin Dropdown Menu -->
                    <?php if (isAdmin()): ?>
                        <div class="dropdown me-2">
                            <button class="btn btn-sm btn-warning text-dark fw-bold dropdown-toggle" type="button"
                                id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-tools me-1"></i> Yönetim
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                                <li><a class="dropdown-item" href="admin_users.php">
                                        <i class="fas fa-users me-2" style="color: #6f42c1;"></i> Kullanıcılar
                                    </a></li>
                                <li><a class="dropdown-item" href="admin_logs.php">
                                        <i class="fas fa-history me-2 text-warning"></i> İşlem Geçmişi
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="admin_settings.php">
                                        <i class="fas fa-cogs me-2 text-secondary"></i> Sistem Ayarları
                                    </a></li>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light fw-bold dropdown-toggle" type="button" id="userDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-1"></i>
                            <span class="d-none d-md-inline"><?php echo htmlspecialchars($currentUser); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <h6 class="dropdown-header">
                                    <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($currentUser); ?>
                                </h6>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="confirmLogout(); return false;">
                                    <i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap
                                </a></li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Çıkış Yapılıyor',
                text: 'Oturumunuz başarıyla sonlandırılıyor.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
                backdrop: `rgba(0,0,123,0.4)`
            }).then(() => {
                window.location.href = 'logout.php';
            });
        }

        // Initialize icon based on current theme
        document.addEventListener('DOMContentLoaded', () => {
            // Clock
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                const dateString = now.toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric', weekday: 'long' });
                const clockEl = document.getElementById('headerClock');
                if (clockEl) clockEl.innerText = `${dateString} | ${timeString}`;
            }
            setInterval(updateClock, 1000);
            updateClock();

            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const icon = document.getElementById('themeIcon');
            if (icon) {
                if (currentTheme === 'dark') {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                } else {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
            }
        });

        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            const icon = document.getElementById('themeIcon');
            if (newTheme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }

        // Oturum Geçerlilik Kontrolü
        function checkSessionValidity() {
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'invalid') {
                        // Oturum geçersiz - kullanıcıyı bilgilendir ve çıkış yap
                        Swal.fire({
                            title: 'Oturum Sonlandırıldı',
                            html: data.message,
                            icon: 'warning',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            confirmButtonText: 'Tamam',
                            confirmButtonColor: '#d33'
                        }).then(() => {
                            window.location.href = 'logout.php';
                        });
                    }
                })
                .catch(error => {
                    console.error('Oturum kontrolü hatası:', error);
                });
        }

        // Her 10 saniyede bir oturum kontrolü yap
        setInterval(checkSessionValidity, 10000);
    </script>

    <div class="container">