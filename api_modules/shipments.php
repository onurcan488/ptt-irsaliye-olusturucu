<?php
function add_shipment($pdo, $user_id)
{
    $waybill_id = $_POST['waybill_id'] ?? 0;
    if (!checkWaybillOwnership($pdo, $waybill_id, $user_id))
        jsonResponse('error', 'Yetkisiz işlem.');

    $document_no = $_POST['document_no'] ?? '';
    $document_type = $_POST['document_type'] ?? 'MUHABERE';
    $receiver_address = $_POST['receiver_address'] ?? '';
    $tracking_number = $_POST['tracking_number'] ?? '';

    if (empty($document_no) || empty($tracking_number))
        jsonResponse('error', 'Evrak No ve Barkod zorunlu.');

    // Barkod benzersizlik kontrolü
    try {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM shipments WHERE tracking_number = ?");
        $checkStmt->execute([$tracking_number]);
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            jsonResponse('error', 'Bu barkod numarası daha önce kullanılmış. Lütfen farklı bir barkod numarası giriniz.');
        }
    } catch (PDOException $e) {
        jsonResponse('error', 'Kontrol hatası: ' . $e->getMessage());
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO shipments (waybill_id, document_no, document_type, receiver_address, tracking_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$waybill_id, $document_no, $document_type, $receiver_address, $tracking_number]);
        $newId = $pdo->lastInsertId();
        logActivity($pdo, $user_id, 'add_shipment', "İrsaliye ID: $waybill_id, Gönderi ID: $newId, Barkod: $tracking_number");
        jsonResponse('success', 'Eklendi.', ['id' => $newId]);
    } catch (PDOException $e) {
        jsonResponse('error', 'Hata: ' . $e->getMessage());
    }
}

function update_shipment($pdo, $user_id)
{
    $id = $_POST['id'] ?? 0;
    $wbId = getWaybillIdFromShipment($pdo, $id);
    if (!$wbId || !checkWaybillOwnership($pdo, $wbId, $user_id))
        jsonResponse('error', 'Yetkisiz işlem.');

    $document_no = $_POST['document_no'] ?? '';
    $document_type = $_POST['document_type'] ?? 'MUHABERE';
    $receiver_address = $_POST['receiver_address'] ?? '';
    $tracking_number = $_POST['tracking_number'] ?? '';

    // Barkod benzersizlik kontrolü (kendi kaydı hariç)
    try {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM shipments WHERE tracking_number = ? AND id != ?");
        $checkStmt->execute([$tracking_number, $id]);
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            jsonResponse('error', 'Bu barkod numarası daha önce kullanılmış. Lütfen farklı bir barkod numarası giriniz.');
        }
    } catch (PDOException $e) {
        jsonResponse('error', 'Kontrol hatası: ' . $e->getMessage());
    }

    try {
        $stmt = $pdo->prepare("UPDATE shipments SET document_no=?, document_type=?, receiver_address=?, tracking_number=? WHERE id=?");
        $stmt->execute([$document_no, $document_type, $receiver_address, $tracking_number, $id]);
        logActivity($pdo, $user_id, 'update_shipment', "Gönderi güncellendi. ID: $id - Barkod: $tracking_number");
        jsonResponse('success', 'Güncellendi.');
    } catch (PDOException $e) {
        jsonResponse('error', 'Güncelleme hatası: ' . $e->getMessage());
    }
}

function delete_shipment($pdo, $user_id)
{
    $id = $_POST['id'] ?? 0;
    $wbId = getWaybillIdFromShipment($pdo, $id);
    if (!$wbId || !checkWaybillOwnership($pdo, $wbId, $user_id))
        jsonResponse('error', 'Yetkisiz işlem.');

    try {
        $stmt = $pdo->prepare("DELETE FROM shipments WHERE id = ?");
        $stmt->execute([$id]);
        logActivity($pdo, $user_id, 'delete_shipment', "Gönderi silindi. ID: $id");
        jsonResponse('success', 'Silindi.');
    } catch (PDOException $e) {
        jsonResponse('error', 'Silme hatası: ' . $e->getMessage());
    }
}

function get_shipments($pdo, $user_id)
{
    $waybill_id = $_GET['waybill_id'] ?? 0;
    if (!checkWaybillOwnership($pdo, $waybill_id, $user_id))
        jsonResponse('error', 'Yetkisiz işlem.');

    $stmt = $pdo->prepare("SELECT * FROM shipments WHERE waybill_id = ? ORDER BY created_at ASC");
    $stmt->execute([$waybill_id]);
    jsonResponse('success', 'OK', $stmt->fetchAll());
}

function search_shipments($pdo, $user_id)
{
    $term = trim($_GET['term'] ?? '');
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    if (strlen($term) < 3)
        jsonResponse('error', 'En az 3 karakter giriniz.');

    $params = [];
    $whereClause = "WHERE ";

    if (!isAdmin()) {
        $params[':uid'] = $user_id;
        $whereClause .= "w.user_id = :uid AND ";
    }

    $whereClause .= "(";

    if (strpos($term, '/') !== false) {
        $parts = explode('/', $term);
        $year = trim($parts[0] ?? '');
        $no = trim($parts[1] ?? '');

        if (!empty($year) && !empty($no)) {
            $whereClause .= "(s.document_no REGEXP :yearRegexp AND s.document_no REGEXP :noRegexp)";
            $params[':yearRegexp'] = '(^|[^0-9])' . preg_quote($year) . '([^0-9]|$)';
            $params[':noRegexp'] = '(^|[^0-9])' . preg_quote($no) . '([^0-9]|$)';
        } else {
            $whereClause .= "(s.tracking_number LIKE :term OR s.document_no LIKE :term OR s.receiver_address LIKE :term)";
            $params[':term'] = '%' . $term . '%';
        }
    } else {
        $whereClause .= "(s.tracking_number LIKE :term OR s.document_no LIKE :term OR s.receiver_address LIKE :term)";
        $params[':term'] = '%' . $term . '%';
    }
    $whereClause .= ")";

    try {
        $countSql = "SELECT COUNT(*) FROM shipments s JOIN waybills w ON s.waybill_id = w.id " . $whereClause;
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalItems = $countStmt->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $sql = "SELECT s.*, w.title as waybill_title, w.created_at as waybill_date, w.status as waybill_status, u.display_name as creator_name 
                FROM shipments s 
                JOIN waybills w ON s.waybill_id = w.id 
                LEFT JOIN users u ON w.user_id = u.id 
                $whereClause 
                ORDER BY s.created_at DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
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

    } catch (PDOException $e) {
        jsonResponse('error', 'Arama hatası: ' . $e->getMessage());
    }
}
?>