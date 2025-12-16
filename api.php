<?php
require_once 'config.php';
require_once 'functions.php';

// Modülleri Dahil Et
require_once 'api_modules/waybills.php';
require_once 'api_modules/shipments.php';
require_once 'api_modules/users.php';
require_once 'api_modules/settings.php';

if (!isLoggedIn()) {
    jsonResponse('error', 'Giriş gerekli.');
}

$user_id = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- LİSTE (WAYBILL) İŞLEMLERİ ---
    if ($action === 'create_draft') {
        create_draft($pdo, $user_id);
    } elseif ($action === 'complete_list') {
        complete_list($pdo, $user_id);
    } elseif ($action === 'delete_list') {
        delete_list($pdo, $user_id);
    }
    // --- GÖNDERİ (SHIPMENT) İŞLEMLERİ ---
    elseif ($action === 'add_shipment') {
        add_shipment($pdo, $user_id);
    } elseif ($action === 'update_shipment') {
        update_shipment($pdo, $user_id);
    } elseif ($action === 'delete_shipment') {
        delete_shipment($pdo, $user_id);
    }
    // --- KULLANICI YÖNETİMİ (Sadece Admin) ---
    elseif ($action === 'add_user') {
        add_user($pdo);
    } elseif ($action === 'update_user') {
        update_user($pdo);
    } elseif ($action === 'delete_user') {
        delete_user($pdo, $user_id);
    }
    // --- AYARLAR ---
    elseif ($action === 'update_general_settings') {
        update_general_settings($pdo);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_lists') {
        get_lists($pdo, $user_id);
    } elseif ($action === 'get_dashboard_stats') {
        get_dashboard_stats($pdo);
    } elseif ($action === 'get_waybill_dates') {
        get_waybill_dates($pdo, $user_id);
    } elseif ($action === 'search_shipments') {
        search_shipments($pdo, $user_id);
    } elseif ($action === 'get_shipments') {
        get_shipments($pdo, $user_id);
    } elseif ($action === 'get_users') {
        get_users($pdo);
    }
}
?>