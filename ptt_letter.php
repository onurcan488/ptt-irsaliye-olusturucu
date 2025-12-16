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

    // Birim adını formatla
    $waybill['display_name'] = formatUnitName($waybill['display_name']);

    $stmt = $pdo->prepare("SELECT * FROM shipments WHERE waybill_id = ? ORDER BY created_at DESC");
    $stmt->execute([$waybill_id]);
    $shipments = $stmt->fetchAll();

    // Ayarlardan kurum adını al
    $settingsStmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'institution_name'");
    $institution_name = $settingsStmt->fetchColumn() ?: 'Araklı Adliyesi';

    // Bugünün tarihi
    $today = date('d.m.Y');
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <title>PTT Üst Yazı - <?php echo escape($waybill['title']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 2.5cm 2cm 2cm 2.5cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            background: white;
            padding: 2.5cm 2cm 2cm 2.5cm;
        }

        /* Üst Bilgi */
        .letterhead {
            text-align: center;
            margin-bottom: 20px;
        }

        .letterhead .tc {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .letterhead .institution {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .letterhead .divider {
            border-bottom: 1.5pt solid #000;
            margin: 10px 0 15px 0;
        }

        /* Sayı ve Tarih */
        .reference-info {
            margin-bottom: 25px;
        }

        .reference-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }

        .reference-label {
            display: table-cell;
            width: 100px;
            font-weight: bold;
        }

        .reference-value {
            display: table-cell;
            padding-left: 10px;
        }

        /* Konu */
        .subject-section {
            margin-bottom: 25px;
            font-weight: bold;
        }

        .subject-label {
            display: inline-block;
            width: 100px;
        }

        .subject-value {
            display: inline;
        }

        /* İlgi */
        .reference-section {
            margin-bottom: 25px;
        }

        .reference-label-text {
            display: inline-block;
            width: 100px;
            font-weight: bold;
        }

        /* Ek */
        .attachment-section {
            margin-bottom: 25px;
        }

        .attachment-label {
            display: inline-block;
            width: 100px;
            font-weight: bold;
        }

        /* Gövde Metni */
        .body-content {
            text-align: justify;
            margin-bottom: 20px;
            line-height: 1.8;
        }

        .body-content p {
            margin-bottom: 15px;
            text-indent: 1cm;
        }

        .body-content p:first-child {
            text-indent: 0;
        }

        /* Sonuç Cümlesi */
        .conclusion {
            text-align: justify;
            margin-bottom: 40px;
            text-indent: 1cm;
        }

        /* İmza Bölümü */
        .signature-block {
            margin-top: 60px;
            text-align: center;
        }

        .signature-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .signature-title {
            font-size: 11pt;
        }

        .signature-line {
            margin-top: 80px;
            margin-bottom: 10px;
        }

        /* Dağıtım */
        .distribution {
            margin-top: 40px;
            font-size: 10pt;
        }

        .distribution-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Yazdır Butonu */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #0066cc;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        .print-button:hover {
            background: #0052a3;
        }

        /* Watermark */
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
            background-size: 500px;
            opacity: 0.1;
            pointer-events: none;
            z-index: -1;
        }

        @media print {
            body {
                padding: 0;
            }

            .print-button {
                display: none;
            }
        }
    </style>
</head>

<body>
    <button class="print-button" onclick="window.print()">🖨️ Yazdır</button>

    <!-- Üst Bilgi -->
    <div class="letterhead">
        <div class="tc">T.C.</div>
        <div class="institution"><?php echo mb_strtoupper(escape($waybill['display_name']), 'UTF-8'); ?></div>
        <div class="divider"></div>
    </div>

    <!-- Sayı, Tarih, Konu -->
    <div class="reference-info">
        <div class="reference-row">
            <div class="reference-label">Sayı</div>
            <div class="reference-value">: <?php echo date('Y'); ?>/<?php echo $waybill_id; ?> İrsaliye</div>
        </div>
        <div class="reference-row">
            <div class="reference-label">Tarih</div>
            <div class="reference-value">: <?php echo $today; ?></div>
        </div>
    </div>

    <div class="subject-section">
        <span class="subject-label">Konu</span>
        <span class="subject-value">: Evrak Gönderimi</span>
    </div>

    <div class="reference-section">
        <span class="reference-label-text">İlgi</span>
        <span>: <?php echo escape($waybill['title']); ?> tarihli irsaliye.</span>
    </div>

    <div class="attachment-section">
        <span class="attachment-label">Ek</span>
        <span>: <?php echo count($shipments); ?> (<?php
            $numbers = [
                '',
                'Bir',
                'İki',
                'Üç',
                'Dört',
                'Beş',
                'Altı',
                'Yedi',
                'Sekiz',
                'Dokuz',
                'On',
                'On Bir',
                'On İki',
                'On Üç',
                'On Dört',
                'On Beş',
                'On Altı',
                'On Yedi',
                'On Sekiz',
                'On Dokuz',
                'Yirmi'
            ];
            $count = count($shipments);
            echo $count <= 20 ? $numbers[$count] : $count;
            ?>) adet irsaliye.</span>
    </div>

    <!-- Gövde -->
    <div class="body-content">
        <p><strong>PTT KARGO</strong></p>
        <p><strong>Araklı Şubesi</strong></p>
        <p>İlgide kayıtlı irsaliye ile birlikte toplam <strong><?php echo count($shipments); ?> (<?php
            $count = count($shipments);
            echo $count <= 20 ? mb_strtolower($numbers[$count], 'UTF-8') : $count;
            ?>)</strong> adet evrak ekte gönderilmiştir.</p>
    </div>

    <div class="conclusion">
        Bilgilerinizi ve gereğini rica ederim.
    </div>

    <!-- İmza -->
    <div class="signature-block">
        <?php if (!empty($waybill['prepared_by'])): ?>
            <div class="signature-line"></div>
            <div class="signature-name"><?php echo mb_strtoupper(escape($waybill['prepared_by']), 'UTF-8'); ?></div>
            <div class="signature-title"><?php echo escape($waybill['display_name']); ?></div>
        <?php else: ?>
            <div class="signature-line"></div>
            <div class="signature-title"><?php echo escape($waybill['display_name']); ?></div>
        <?php endif; ?>
    </div>

    <!-- Dağıtım (Opsiyonel) -->
    <div class="distribution">
        <div class="distribution-title">DAĞITIM:</div>
        <div>PTT Kargo Araklı Şubesi</div>
    </div>

</body>

</html>