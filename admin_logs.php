<?php
require_once 'header.php';
require_once 'config.php';

if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Sayfalama
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtreler
$filter_date = $_GET['date'] ?? '';
$filter_user = $_GET['user'] ?? '';
$filter_action = $_GET['action'] ?? '';

// Filtre SQL Oluşturma
$where = "WHERE 1=1";
$params = [];

// Tarih Filtresi
// Tarih Filtresi
if (!empty($filter_date)) {
    if (strpos($filter_date, ' to ') !== false) {
        list($start, $end) = explode(' to ', $filter_date);
        $where .= " AND DATE(l.created_at) BETWEEN ? AND ?";
        $params[] = trim($start);
        $params[] = trim($end);
    } elseif (strpos($filter_date, ' - ') !== false) {
        list($start, $end) = explode(' - ', $filter_date);
        $where .= " AND DATE(l.created_at) BETWEEN ? AND ?";
        $params[] = trim($start);
        $params[] = trim($end);
    } else {
        $where .= " AND DATE(l.created_at) = ?";
        $params[] = trim($filter_date);
    }
}

// Kullanıcı Filtresi
if (!empty($filter_user)) {
    $where .= " AND l.user_id = ?";
    $params[] = $filter_user;
}

// Aksiyon Filtresi
if (!empty($filter_action)) {
    $where .= " AND l.action = ?";
    $params[] = $filter_action;
}

try {
    $countSql = "SELECT COUNT(*) FROM activity_logs l $where";
    $stmtCount = $pdo->prepare($countSql);
    $stmtCount->execute($params);
    $total = $stmtCount->fetchColumn();
    $totalPages = ceil($total / $limit);

    $sql = "
        SELECT l.*, u.display_name, u.username 
        FROM activity_logs l 
        LEFT JOIN users u ON l.user_id = u.id 
        $where
        ORDER BY l.created_at DESC 
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Kullanıcı listesi (Filtre dropdown için)
    $users = $pdo->query("SELECT id, display_name FROM users ORDER BY display_name ASC")->fetchAll(PDO::FETCH_KEY_PAIR);

    // Aksiyon listesi (Dropdown için)
    $actions = [
        'login' => 'Giriş',
        'logout' => 'Çıkış',
        'create_waybill' => 'İrsaliye Oluşturma',
        'complete_waybill' => 'İrsaliye Tamamlama',
        'delete_waybill' => 'İrsaliye Silme',
        'add_shipment' => 'Gönderi Ekleme',
        'update_shipment' => 'Gönderi Güncelleme',
        'delete_shipment' => 'Gönderi Silme',
        'add_user' => 'Kullanıcı Ekleme',
        'update_user' => 'Kullanıcı Güncelleme',
        'delete_user' => 'Kullanıcı Silme',
        'update_settings' => 'Ayarları Güncelleme',
        'baska_cihazdan_giris_ile_oturum_sonlandirildi' => 'Oturum Çakışması (Sonlandırıldı)',
        'es_zamanli_giris_denemesi' => 'Eşzamanlı Giriş Denemesi'
    ];

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Hata: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $logs = [];
}
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-warning"></i>İşlem Geçmişi</h2>
        <p class="text-muted mb-0">Kullanıcıların sistem üzerindeki tüm aktiviteleri.</p>
    </div>
    <div class="col-md-6 text-end">
    </div>
</div>

<!-- Filtreler -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Tarih Aralığı</label>
                <input type="text" name="date" class="form-control" id="dateFilter" placeholder="Tarih seçiniz..."
                    value="<?php echo htmlspecialchars($filter_date); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Kullanıcı</label>
                <select name="user" class="form-select">
                    <option value="">Tümü</option>
                    <?php foreach ($users as $uid => $uname): ?>
                        <option value="<?php echo $uid; ?>" <?php echo $filter_user == $uid ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($uname); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">İşlem Türü</label>
                <select name="action" class="form-select">
                    <option value="">Tümü</option>
                    <?php foreach ($actions as $key => $val): ?>
                        <option value="<?php echo $key; ?>" <?php echo $filter_action == $key ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($val); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filtrele</button>
                <?php if (!empty($filter_date) || !empty($filter_user) || !empty($filter_action)): ?>
                    <a href="admin_logs.php" class="btn btn-light w-100 mt-2"><i class="fas fa-times me-1"></i> Temizle</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        flatpickr("#dateFilter", {
            mode: "range",
            dateFormat: "Y-m-d",
            locale: "tr",
            altInput: true,
            altFormat: "d.m.Y",
            allowInput: true
        });

        // Logları her 5 saniyede bir sessizce güncelle
        setInterval(() => {
            // Mevcut URL'i koru (filtreler ve sayfa numarası dahil)
            window.location.reload();
        }, 5000);
    });
</script>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">Tarih</th>
                        <th scope="col">Kullanıcı</th>
                        <th scope="col">İşlem</th>
                        <th scope="col">Detaylar</th>
                        <th scope="col">IP Adresi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Henüz kayıt yok.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="ps-4 text-nowrap"><?php echo date('d.m.Y H:i:s', strtotime($log['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($log['display_name'] ?? 'Bilinmiyor'); ?>
                                    </div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($log['username'] ?? '-'); ?></div>
                                </td>
                                <td>
                                    <?php
                                    $badges = [
                                        'login' => 'bg-success',
                                        'logout' => 'bg-secondary',
                                        'create_waybill' => 'bg-primary',
                                        'complete_waybill' => 'bg-info text-dark',
                                        'delete_waybill' => 'bg-danger',
                                        'add_shipment' => 'bg-primary bg-opacity-75',
                                        'update_shipment' => 'bg-warning text-dark',
                                        'delete_shipment' => 'bg-danger bg-opacity-75',
                                        'add_user' => 'bg-success bg-gradient',
                                        'update_user' => 'bg-warning bg-gradient text-dark',
                                        'delete_user' => 'bg-danger bg-gradient',
                                        'update_settings' => 'bg-dark',
                                        'baska_cihazdan_giris_ile_oturum_sonlandirildi' => 'bg-danger border border-light',
                                        'es_zamanli_giris_denemesi' => 'bg-warning text-dark border border-dark'
                                    ];
                                    $badge = $badges[$log['action']] ?? 'bg-secondary';
                                    ?>
                                    <span
                                        class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($actions[$log['action']] ?? $log['action']); ?></span>
                                </td>
                                <td>
                                    <div class="text-break" style="max-width: 400px; font-size: 0.9em;">
                                        <?php echo htmlspecialchars($log['details']); ?>
                                    </div>
                                </td>
                                <td class="text-muted small"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<div class="card shadow-sm border-0 mb-4 mt-3">
    <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <span class="text-muted small">Toplam <b><?php echo $total; ?></b> kayıt, Sayfa <b><?php echo $page; ?></b>
                / <?php echo max(1, $totalPages); ?></span>
        </div>

        <?php if ($totalPages > 1): ?>
            <?php
            $qs = http_build_query([
                'date' => $filter_date,
                'user' => $filter_user,
                'action' => $filter_action
            ]);
            ?>
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo $qs; ?>">Önceki</a>
                    </li>

                    <?php
                    // Akıllı Sayfalama
                    $paginationMap = [];
                    if ($totalPages <= 7) {
                        for ($i = 1; $i <= $totalPages; $i++)
                            $paginationMap[] = $i;
                    } else {
                        if ($page <= 4) {
                            $paginationMap = [1, 2, 3, 4, 5, '...', $totalPages];
                        } elseif ($page >= $totalPages - 3) {
                            $paginationMap = [1, '...', $totalPages - 4, $totalPages - 3, $totalPages - 2, $totalPages - 1, $totalPages];
                        } else {
                            $paginationMap = [1, '...', $page - 1, $page, $page + 1, '...', $totalPages];
                        }
                    }
                    ?>

                    <?php foreach ($paginationMap as $p): ?>
                        <?php if ($p === '...'): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php else: ?>
                            <li class="page-item <?php echo $page == $p ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $p; ?>&<?php echo $qs; ?>"><?php echo $p; ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo $qs; ?>">Sonraki</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>