<?php
session_start();
require_once 'logging_utils.php';

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function getCurrentUser()
{
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'display_name' => $_SESSION['display_name'],
            'role' => $_SESSION['role'] ?? 'user'
        ];
    }
    return null;
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function escape($text)
{
    if (is_null($text))
        return '';
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function jsonResponse($status, $message, $data = [])
{
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

function checkWaybillOwnership($pdo, $wbId, $userId)
{
    if (isAdmin())
        return true;
    $stmt = $pdo->prepare("SELECT id FROM waybills WHERE id = ? AND user_id = ?");
    $stmt->execute([$wbId, $userId]);
    return $stmt->fetch() !== false;
}

function getWaybillIdFromShipment($pdo, $shipmentId)
{
    $stmt = $pdo->prepare("SELECT waybill_id FROM shipments WHERE id = ?");
    $stmt->execute([$shipmentId]);
    return $stmt->fetchColumn();
}

// Ayarlar Fonksiyonları
function getSetting($key)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn() ?: '';
}

function setSetting($key, $value)
{
    global $pdo;
    $stmt = $pdo->prepare("REPLACE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    $stmt->execute([$key, $value]);
}

function getCityPrefix()
{
    $inst = getSetting('institution_name');
    if (empty($inst))
        return '';
    $parts = explode(' ', trim($inst));
    return $parts[0] ?? '';
}

function formatUnitName($name)
{
    // Admin veya özel isimler için kontrol eklenebilir
    if ($name === 'Sistem Yöneticisi' || empty($name))
        return $name;

    $city = getCityPrefix();
    if (empty($city))
        return $name;

    // Eğer isim zaten şehir adıyla başlıyorsa tekrar ekleme
    if (mb_stripos($name, $city) === 0)
        return $name;

    return $city . ' ' . $name;
}

// Oturum Yönetimi Fonksiyonları
function createUserSession($pdo, $userId, $ipAddress, $userAgent)
{
    // Önce bu kullanıcının tüm eski oturumlarını sil
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
    $stmt->execute([$userId]);

    // Yeni oturum token'ı oluştur
    $sessionToken = bin2hex(random_bytes(32));

    // Yeni oturum kaydı oluştur
    $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $sessionToken, $ipAddress, $userAgent]);

    // Session'a token'ı kaydet
    $_SESSION['session_token'] = $sessionToken;

    return $sessionToken;
}

function checkActiveSession($pdo, $userId, $currentIp)
{
    // Bu kullanıcının aktif bir oturumu var mı kontrol et
    $stmt = $pdo->prepare("SELECT ip_address, last_activity FROM user_sessions WHERE user_id = ? AND ip_address != ?");
    $stmt->execute([$userId, $currentIp]);
    $activeSession = $stmt->fetch();

    if ($activeSession) {
        // Son aktiviteden 30 dakika geçmişse oturumu geçersiz say
        $lastActivity = strtotime($activeSession['last_activity']);
        $now = time();
        $diff = $now - $lastActivity;

        if ($diff < 1800) { // 30 dakika = 1800 saniye
            return [
                'active' => true,
                'ip' => $activeSession['ip_address']
            ];
        } else {
            // Eski oturumu sil
            $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
            $stmt->execute([$userId]);
        }
    }

    return ['active' => false];
}

function validateUserSession($pdo)
{
    if (!isLoggedIn()) {
        return false;
    }

    $userId = $_SESSION['user_id'];
    $sessionToken = $_SESSION['session_token'] ?? '';
    $currentIp = $_SERVER['REMOTE_ADDR'];

    // Session token kontrolü
    $stmt = $pdo->prepare("SELECT ip_address FROM user_sessions WHERE user_id = ? AND session_token = ?");
    $stmt->execute([$userId, $sessionToken]);
    $session = $stmt->fetch();

    if (!$session) {
        // Geçersiz oturum - çıkış yap
        destroyUserSession($pdo);
        return false;
    }

    // IP adresi değişmiş mi kontrol et
    if ($session['ip_address'] !== $currentIp) {
        // IP değişmiş - güvenlik nedeniyle çıkış yap
        destroyUserSession($pdo);
        return false;
    }

    // Oturum geçerli - son aktivite zamanını güncelle
    $stmt = $pdo->prepare("UPDATE user_sessions SET last_activity = NOW() WHERE user_id = ? AND session_token = ?");
    $stmt->execute([$userId, $sessionToken]);

    return true;
}

function destroyUserSession($pdo)
{
    if (isset($_SESSION['user_id']) && isset($_SESSION['session_token'])) {
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ? AND session_token = ?");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);
    }

    session_unset();
    session_destroy();
}
?>