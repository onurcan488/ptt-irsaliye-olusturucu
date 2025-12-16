<?php
function add_user($pdo)
{
    if (!isAdmin())
        jsonResponse('error', 'Bu işlem için yetkiniz yok.');

    $u = trim($_POST['username'] ?? '');
    $d = trim($_POST['display_name'] ?? '');
    $p = $_POST['password'] ?? '';
    $r = $_POST['role'] ?? 'user';

    if (empty($u) || empty($d) || empty($p))
        jsonResponse('error', 'Eksik alanlar.');

    // Admin rolü kontrolü - Sistemde sadece bir admin olabilir
    if ($r === 'admin') {
        $adminCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $adminCheck->execute();
        $adminCount = $adminCheck->fetchColumn();
        if ($adminCount > 0) {
            jsonResponse('error', 'Sistemde zaten bir Yönetici bulunmaktadır. Birden fazla yönetici eklenemez.');
        }
    }

    $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$u]);
    if ($check->rowCount() > 0)
        jsonResponse('error', 'Bu kullanıcı adı zaten kullanılıyor.');

    try {
        $hash = password_hash($p, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, display_name, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$u, $d, $hash, $r]);
        logActivity($pdo, $_SESSION['user_id'], 'add_user', "Yeni kullanıcı eklendi: $u ($d)");
        jsonResponse('success', 'Kullanıcı eklendi.');
    } catch (PDOException $e) {
        jsonResponse('error', 'Hata: ' . $e->getMessage());
    }
}

function update_user($pdo)
{
    if (!isAdmin())
        jsonResponse('error', 'Bu işlem için yetkiniz yok.');

    $id = $_POST['id'] ?? 0;
    $u = trim($_POST['username'] ?? '');
    $d = trim($_POST['display_name'] ?? '');
    $p = $_POST['password'] ?? '';
    $r = $_POST['role'] ?? 'user';

    // Mevcut kullanıcının rolünü kontrol et
    $currentUserStmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $currentUserStmt->execute([$id]);
    $currentRole = $currentUserStmt->fetchColumn();

    // Admin rolü kontrolü - Sistemde sadece bir admin olabilir
    if ($r === 'admin' && $currentRole !== 'admin') {
        // Kullanıcı admin yapılmaya çalışılıyor
        $adminCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $adminCheck->execute();
        $adminCount = $adminCheck->fetchColumn();
        if ($adminCount > 0) {
            jsonResponse('error', 'Sistemde zaten bir Yönetici bulunmaktadır. Birden fazla yönetici olamaz.');
        }
    }

    try {
        if (!empty($p)) {
            $hash = password_hash($p, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username=?, display_name=?, role=?, password_hash=? WHERE id=?");
            $stmt->execute([$u, $d, $r, $hash, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, display_name=?, role=? WHERE id=?");
            $stmt->execute([$u, $d, $r, $id]);
        }
        logActivity($pdo, $_SESSION['user_id'], 'update_user', "Kullanıcı güncellendi. ID: $id - Kullanıcı Adı: $u");
        jsonResponse('success', 'Kullanıcı güncellendi.');
    } catch (PDOException $e) {
        jsonResponse('error', 'Hata: ' . $e->getMessage());
    }
}

function delete_user($pdo, $current_user_id)
{
    if (!isAdmin())
        jsonResponse('error', 'Bu işlem için yetkiniz yok.');

    $id = $_POST['id'] ?? 0;
    if ($id == $current_user_id)
        jsonResponse('error', 'Kendinizi silemezsiniz.');

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        logActivity($pdo, $current_user_id, 'delete_user', "Kullanıcı silindi. ID: $id");
        jsonResponse('success', 'Kullanıcı silindi.');
    } catch (PDOException $e) {
        jsonResponse('error', 'Hata: ' . $e->getMessage());
    }
}

function get_users($pdo)
{
    if (!isAdmin())
        jsonResponse('error', 'Bu işlem için yetkiniz yok.');

    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $offset = ($page - 1) * $limit;

    try {
        $whereSql = "WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $whereSql .= " AND (username LIKE ? OR display_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Toplam sayıyı al
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereSql");
        $countStmt->execute($params);
        $totalItems = $countStmt->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        // Verileri al
        // LIMIT ve OFFSET'i güvenli bir şekilde (int) cast ederek sorguya ekliyoruz.
        $sql = "SELECT id, username, display_name, role, created_at FROM users $whereSql ORDER BY display_name ASC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        jsonResponse('success', 'OK', [
            'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems
            ]
        ]);
    } catch (PDOException $e) {
        jsonResponse('error', 'Veri çekme hatası: ' . $e->getMessage());
    }
}
?>