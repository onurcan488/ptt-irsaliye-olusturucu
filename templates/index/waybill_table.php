<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Durum</th>
                        <th>İrsaliye Adı</th>
                        <?php if (isAdmin())
                            echo '<th>Oluşturan Birim</th>'; ?>
                        <th>Oluşturma Tarihi</th>
                        <th class="text-center">Gönderi Sayısı</th>
                        <th class="text-end pe-4">İşlemler</th>
                    </tr>
                </thead>
                <tbody id="waybillTableBody">
                    <tr>
                        <td colspan="<?php echo isAdmin() ? 6 : 5; ?>" class="text-center py-5">
                            <div class="spinner-border text-primary"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-between align-items-center">
        <div id="paginationInfo" class="mb-0"></div>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mb-0" id="paginationControls">
                <!-- Sayfalama buraya gelecek -->
            </ul>
        </nav>
    </div>
</div>