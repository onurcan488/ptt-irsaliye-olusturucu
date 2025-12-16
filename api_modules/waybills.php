<?php
function create_draft($pdo, $user_id)
{
    if (isAdmin())
        jsonResponse('error', 'Yöneticiler irsaliye oluşturamaz.');

    $title = '#' . time() . ' - Yeni İrsaliye';
    try {
        $stmt = $pdo->prepare("INSERT INTO waybills (title, status, user_id) VALUES (?, 'draft', ?)");
        $stmt->execute([$title, $user_id]);
        $newId = $pdo->lastInsertId();
        logActivity($pdo, $user_id, 'create_waybill', 'Yeni taslak oluşturuldu. ID: ' . $newId);
        jsonResponse('success', 'Taslak oluşturuldu.', ['id' => $newId]);
    } catch (PDOException $e) {
        jsonResponse('error', 'Oluşturma hatası: ' . $e->getMessage());
    }
}

function complete_list($pdo, $user_id)
{
    $id = $_POST['id'] ?? 0;
    $custom_title = $_POST['title'] ?? '';
    $prepared_by = $_POST['prepared_by'] ?? '';
    $delivered_by = $_POST['delivered_by'] ?? '';
    $received_by = $_POST['received_by'] ?? '';

    if (!checkWaybillOwnership($pdo, $id, $user_id))
        jsonResponse('error', 'Yetkisiz işlem.');

    try {
        if (empty($custom_title)) {
            $custom_title = date('d.m.Y H:i') . ' İrsaliyesi';
        }

        // İrsaliye başlık kontrolü (Aynı kullanıcı için aynı isimde irsaliye olamaz)
        // Öncelikle irsaliyenin sahibini bulalım (Admin düzenliyorsa diye)
        $ownerStmt = $pdo->prepare("SELECT user_id FROM waybills WHERE id = ?");
        $ownerStmt->execute([$id]);
        $ownerId = $ownerStmt->fetchColumn();

        $dupStmt = $pdo->prepare("SELECT COUNT(*) FROM waybills WHERE title = ? AND user_id = ? AND id != ?");
        $dupStmt->execute([$custom_title, $ownerId, $id]);
        if ($dupStmt->fetchColumn() > 0) {
            jsonResponse('error', 'Bu isimde bir irsaliye zaten mevcut. Lütfen farklı bir isim belirleyiniz.');
        }

        $stmt = $pdo->prepare("UPDATE waybills SET status = 'completed', title = ?, completed_at = NOW(), prepared_by = ?, delivered_by = ?, received_by = ? WHERE id = ?");
        $stmt->execute([$custom_title, $prepared_by, $delivered_by, $received_by, $id]);
        logActivity($pdo, $user_id, 'complete_waybill', "İrsaliye tamamlandı. ID: $id - Başlık: $custom_title");
        jsonResponse('success', 'İrsaliye tamamlandı.');
    } catch (PDOException $e) {
        jsonResponse('error', 'Güncelleme hatası: ' . $e->getMessage());
    }
}

function delete_list($pdo, $user_id)
{
    $id = $_POST['id'] ?? 0;

    if (!isAdmin()) {
        // Normal kullanıcılar sadece KENDİ TASLAKLARINI silebilir
        $stmt = $pdo->prepare("SELECT user_id, status FROM waybills WHERE id = ?");
        $stmt->execute([$id]);
        $wb = $stmt->fetch();

        if (!$wb) {
            jsonResponse('error', 'İrsaliye bulunamadı.');
        }

        if ($wb['user_id'] != $user_id) {
            jsonResponse('error', 'Bu işlem için yetkiniz yok (Size ait değil).');
        }

        if ($wb['status'] !== 'draft') {
            jsonResponse('error', 'Sadece taslak modundaki irsaliyeleri silebilirsiniz. Tamamlanmış irsaliyeler silinemez.');
        }
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM waybills WHERE id = ?");
        $stmt->execute([$id]);
        logActivity($pdo, $user_id, 'delete_waybill', "İrsaliye silindi. ID: $id");
        jsonResponse('success', 'Silindi.');
    } catch (PDOException $e) {
        jsonResponse('error', 'Silme hatası: ' . $e->getMessage());
    }
}

function get_lists($pdo, $user_id)
{
    try {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $date = isset($_GET['date']) ? trim($_GET['date']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $filterUser = isset($_GET['user']) ? (int) $_GET['user'] : 0;

        $offset = ($page - 1) * $limit;
        $isAdmin = isAdmin();
        $params = [];

        // Tarih Filtresi - Tarih aralığı desteği
        $startDate = null;
        $endDate = null;
        if (!empty($date)) {
            if (strpos($date, ' to ') !== false) {
                list($startDate, $endDate) = explode(' to ', $date);
                $startDate = trim($startDate);
                $endDate = trim($endDate);
            } elseif (strpos($date, ' - ') !== false) {
                list($startDate, $endDate) = explode(' - ', $date);
                $startDate = trim($startDate);
                $endDate = trim($endDate);
            } else {
                $startDate = $endDate = trim($date);
            }
        }

        if ($isAdmin) {
            $whereSql = "WHERE 1=1";

            if (!empty($startDate) && !empty($endDate)) {
                $whereSql .= " AND DATE(w.created_at) BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }

            if (!empty($status)) {
                $whereSql .= " AND w.status = :status";
                $params[':status'] = $status;
            }

            if (!empty($filterUser)) {
                $whereSql .= " AND w.user_id = :fUser";
                $params[':fUser'] = $filterUser;
            }

            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM waybills w $whereSql");
            $countStmt->execute($params);
            $totalItems = $countStmt->fetchColumn();

            $sql = "
                SELECT w.*, u.display_name as creator_name, 
                (SELECT COUNT(*) FROM shipments WHERE waybill_id = w.id) as item_count 
                FROM waybills w
                LEFT JOIN users u ON w.user_id = u.id
                $whereSql
                ORDER BY w.status ASC, w.created_at DESC 
                LIMIT :limit OFFSET :offset
            ";
        } else {
            $whereSql = "WHERE user_id = :uid";
            $params[':uid'] = $user_id;

            if (!empty($startDate) && !empty($endDate)) {
                $whereSql .= " AND DATE(created_at) BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }

            if (!empty($status)) {
                $whereSql .= " AND status = :status";
                $params[':status'] = $status;
            }

            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM waybills $whereSql");
            $countStmt->execute($params);
            $totalItems = $countStmt->fetchColumn();

            $sql = "
                SELECT *, (SELECT COUNT(*) FROM shipments WHERE waybill_id = waybills.id) as item_count 
                FROM waybills 
                $whereSql
                ORDER BY status ASC, created_at DESC 
                LIMIT :limit OFFSET :offset
            ";
        }

        $totalPages = ceil($totalItems / $limit);
        $stmt = $pdo->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($items as &$i) {
            if (isset($i['creator_name'])) {
                $i['creator_name'] = formatUnitName($i['creator_name']);
            }
        }

        jsonResponse('success', 'OK', [
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems
            ]
        ]);
    } catch (Exception $e) {
        jsonResponse('error', 'Hata: ' . $e->getMessage());
    }
}

function get_dashboard_stats($pdo)
{
    if (!isAdmin())
        jsonResponse('error', 'Yetkisiz erişim.');

    try {
        $stats = [];
        $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
        $stats['total_waybills'] = $pdo->query("SELECT COUNT(*) FROM waybills")->fetchColumn();
        $stats['total_shipments'] = $pdo->query("SELECT COUNT(*) FROM shipments")->fetchColumn();
        $stats['completed_waybills'] = $pdo->query("SELECT COUNT(*) FROM waybills WHERE status='completed'")->fetchColumn();

        jsonResponse('success', 'OK', $stats);
    } catch (PDOException $e) {
        jsonResponse('error', 'Hata: ' . $e->getMessage());
    }
}

function get_waybill_dates($pdo, $user_id)
{
    if (isAdmin()) {
        $stmt = $pdo->query("SELECT DISTINCT DATE(created_at) as date FROM waybills");
    } else {
        $stmt = $pdo->prepare("SELECT DISTINCT DATE(created_at) as date FROM waybills WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }

    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    jsonResponse('success', 'OK', $dates);
}
?>