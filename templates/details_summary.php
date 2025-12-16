<div class="row mb-4 align-items-center bg-white p-3 rounded shadow-sm border">
    <div class="col-md-6">
        <a href="index.php" class="text-decoration-none text-muted small fw-bold text-uppercase"><i
                class="fas fa-arrow-left me-1"></i> İrsaliye Listesi</a>
        <div class="mt-2">
            <?php if ($isDraft): ?>
                <span class="badge bg-warning text-dark me-2">TASLAK MODU</span>
                <span class="text-muted small">Bu irsaliye henüz tamamlanmadı.</span>
            <?php else: ?>
                <span class="badge bg-success me-2">TAMAMLANDI</span>
                <h5 class="d-inline-block fw-bold mb-0"><?php echo htmlspecialchars($waybill['title']); ?></h5>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <?php if ($isDraft): ?>
            <button class="btn btn-outline-success me-2" onclick="completeWaybill()">
                <i class="fas fa-check-circle me-1"></i> İrsaliyeyi Tamamla
            </button>
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus me-1"></i> Yeni Gönderi
            </button>
        <?php else: ?>
            <?php if (isAdmin()): ?>
                <button class="btn btn-outline-primary me-2" onclick="openAddModal()">
                    <i class="fas fa-plus me-1"></i> Yeni Gönderi
                </button>
            <?php endif; ?>

            <div class="btn-group me-2" role="group">
                <button onclick="printWaybill(<?php echo $waybill_id; ?>)" class="btn btn-dark">
                    <i class="fas fa-print me-1"></i> İrsaliye Yazdır
                </button>
                <button onclick="downloadPDF(<?php echo $waybill_id; ?>, 'waybill')" class="btn btn-outline-dark"
                    title="İrsaliye PDF İndir">
                    <i class="fas fa-download"></i>
                </button>
            </div>

            <div class="btn-group" role="group">
                <button onclick="printPTTLetter(<?php echo $waybill_id; ?>)" class="btn btn-primary">
                    <i class="fas fa-file-alt me-1"></i> Üst Yazı Yazdır
                </button>
                <button onclick="downloadPDF(<?php echo $waybill_id; ?>, 'letter')" class="btn btn-outline-primary"
                    title="Üst Yazı PDF İndir">
                    <i class="fas fa-download"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>