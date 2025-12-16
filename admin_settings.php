<?php
require_once 'header.php';
require_once 'config.php';

if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

$instName = getSetting('institution_name');
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="mb-0 fw-bold"><i class="fas fa-cogs me-2 text-secondary"></i>Sistem Ayarları</h2>
        <p class="text-muted mb-0">Genel sistem yapılandırması ve tercihler.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8 mx-auto">

        <!-- Genel Ayarlar Kartı -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold text-primary mb-0"><i class="fas fa-university me-2"></i>Kurum Bilgileri</h5>
            </div>
            <div class="card-body p-4">
                <form id="generalSettingsForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kurum Adı (Footer ve Başlıklarda Görünür)</label>
                        <input type="text" name="institution_name" class="form-control form-control-lg"
                            value="<?php echo escape($instName); ?>" required>
                        <div class="form-text">Örn: "Trabzon Adliyesi" veya "Araklı Adliyesi"</div>
                    </div>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save me-1"></i>
                        Kaydet</button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    document.getElementById('generalSettingsForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('action', 'update_general_settings'); // API action handling changed to POST checks but we usually pass action in query or body. code uses request param action
        // Adjust api.php to check POST body for action or GET param.
        // My previous api.php code checks $action  = $_REQUEST['action']. So appending to FormData works.

        fetch('api.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    Swal.fire('Başarılı', res.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Hata', res.message, 'error');
                }
            });
    });
</script>

<?php require_once 'footer.php'; ?>