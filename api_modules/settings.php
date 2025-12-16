<?php
function update_general_settings($pdo)
{
    if (!isAdmin())
        jsonResponse('error', 'Bu işlem için yetkiniz yok.');

    $name = trim($_POST['institution_name'] ?? '');

    if (empty($name))
        jsonResponse('error', 'Kurum adı boş olamaz.');

    try {
        setSetting('institution_name', $name);
        logActivity($pdo, $_SESSION['user_id'], 'update_settings', "Kurum adı güncellendi: $name");
        jsonResponse('success', 'Ayarlar güncellendi.');
    } catch (PDOException $e) {
        jsonResponse('error', 'Hata: ' . $e->getMessage());
    }
}

