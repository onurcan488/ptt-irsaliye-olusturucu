<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin(); // Oturum kontrolü
$user_id = $_SESSION['user_id'];

$waybill_id = $_GET['id'] ?? 0;
if (!$waybill_id)
    die("Liste ID eksik.");

try {
    // Sadece kendi irsaliyesini görüntüleyebilsin ve birim adını çeksin
    // Admin, herkesi görebilir
    if (isAdmin()) {
        $wbStmt = $pdo->prepare("
            SELECT w.*, u.display_name 
            FROM waybills w 
            JOIN users u ON w.user_id = u.id 
            WHERE w.id = ?
        ");
        $wbStmt->execute([$waybill_id]);
    } else {
        $wbStmt = $pdo->prepare("
            SELECT w.*, u.display_name 
            FROM waybills w 
            JOIN users u ON w.user_id = u.id 
            WHERE w.id = ? AND w.user_id = ?
        ");
        $wbStmt->execute([$waybill_id, $user_id]);
    }

    $waybill = $wbStmt->fetch();

    if (!$waybill)
        die("Liste bulunamadı veya yetkiniz yok.");

    // Birim adını formatla (Örn: Araklı Asliye -> Trabzon Asliye)
    $waybill['display_name'] = formatUnitName($waybill['display_name']);

    $stmt = $pdo->prepare("SELECT * FROM shipments WHERE waybill_id = ? ORDER BY created_at DESC");
    $stmt->execute([$waybill_id]);
    $shipments = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <title><?php echo escape($waybill['title']); ?></title>
    <!-- Barkod Fontu: Libre Barcode 39 -->
    <!-- JsBarcode -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <?php include 'templates/print/styles.php'; ?>
</head>

<body>

    <?php include 'templates/print/controls.php'; ?>

    <div class="page">
        <div class="content-wrap">
            <?php include 'templates/print/header.php'; ?>
            <?php include 'templates/print/table.php'; ?>
        </div>

        <?php include 'templates/print/footer.php'; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            JsBarcode(".barcode-svg").init();
        });
    </script>

</body>

</html>