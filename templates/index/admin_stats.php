<!-- Admin Dashboard -->
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i>Yönetim Paneli</h2>
        <p class="text-muted mb-0">Sistem genel durumu ve istatistikler.</p>
    </div>
</div>


<div class="row mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 stat-card stat-card-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-title text-uppercase small fw-bold">Toplam Birim</h6>
                        <h2 class="display-6 fw-bold mb-0" id="statUsers">-</h2>
                    </div>
                    <div class="opacity-50">
                        <i class="fas fa-users fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 stat-card stat-card-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-title text-uppercase small fw-bold">Top. İrsaliye</h6>
                        <h2 class="display-6 fw-bold mb-0" id="statWaybills">-</h2>
                    </div>
                    <div class="opacity-50">
                        <i class="fas fa-file-invoice fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 stat-card stat-card-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-title text-uppercase small fw-bold">Top. Gönderi</h6>
                        <h2 class="display-6 fw-bold mb-0" id="statShipments">-</h2>
                    </div>
                    <div class="opacity-50">
                        <i class="fas fa-box-open fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 stat-card stat-card-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-title text-uppercase small fw-bold">Tamamlanan</h6>
                        <h2 class="display-6 fw-bold mb-0" id="statCompleted">-</h2>
                    </div>
                    <div class="opacity-50">
                        <i class="fas fa-check-circle fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/index/filters.php'; ?>