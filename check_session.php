<?php
require_once 'functions.php';
require_once 'config.php';

header('Content-Type: application/json');

// Kullanıcı giriş yapmamışsa
if (!isLoggedIn()) {
    echo json_encode([
        'status' => 'invalid',
        'message' => 'Oturumunuz sona ermiş. Lütfen tekrar giriş yapın.'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$sessionToken = $_SESSION['session_token'] ?? '';
$currentIp = $_SERVER['REMOTE_ADDR'];

try {
    // Session token kontrolü
    $stmt = $pdo->prepare("SELECT ip_address, last_activity FROM user_sessions WHERE user_id = ? AND session_token = ?");
    $stmt->execute([$userId, $sessionToken]);
    $session = $stmt->fetch();

    if (!$session) {
        // Oturum bulunamadı - başka bir yerden giriş yapılmış
        // Güvenlik logu kaydet
        $logDetails = sprintf(
            'Oturum sonlandırıldı: Başka bir cihazdan giriş yapıldı. Mevcut IP: %s',
            $currentIp
        );
        logActivity($pdo, $userId, 'baska_cihazdan_giris_ile_oturum_sonlandirildi', $logDetails);

        echo json_encode([
            'status' => 'invalid',
            'message' => '<strong>Hesabınıza başka bir cihazdan giriş yapıldı.</strong><br>Güvenlik nedeniyle oturumunuz sonlandırılıyor.'
        ]);
        exit;
    }

    // IP adresi değişmiş mi kontrol et
    if ($session['ip_address'] !== $currentIp) {
        echo json_encode([
            'status' => 'invalid',
            'message' => '<strong>IP adresiniz değişti.</strong><br>Güvenlik nedeniyle oturumunuz sonlandırılıyor.'
        ]);
        exit;
    }

    // Son aktiviteden 30 dakika geçmiş mi kontrol et
    $lastActivity = strtotime($session['last_activity']);
    $now = time();
    $diff = $now - $lastActivity;

    if ($diff > 1800) { // 30 dakika
        echo json_encode([
            'status' => 'invalid',
            'message' => '<strong>Oturumunuz zaman aşımına uğradı.</strong><br>Lütfen tekrar giriş yapın.'
        ]);
        exit;
    }

    // Her şey yolunda
    echo json_encode([
        'status' => 'valid',
        'message' => 'Oturum geçerli'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Bir hata oluştu: ' . $e->getMessage()
    ]);
}
?>