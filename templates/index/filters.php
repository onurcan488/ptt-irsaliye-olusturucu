<div class="row align-items-center mb-3 g-2">
    <div class="col-md-3">
        <h5 class="fw-bold text-muted mb-0">Tüm İrsaliye Hareketleri</h5>
    </div>
    <div class="col-md-9 text-end">
        <div class="d-flex justify-content-end gap-2 flex-wrap">

            <?php if (isAdmin()): ?>
                <?php
                // Fetch users for filter
                $fUsers = $pdo->query("SELECT id, display_name FROM users WHERE role != 'admin' ORDER BY display_name ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
                ?>
                <select id="filterUser" class="form-select w-auto" onchange="loadLists(1)">
                    <option value="">Tüm Kullanıcılar</option>
                    <?php foreach ($fUsers as $uid => $uname): ?>
                        <option value="<?php echo $uid; ?>"><?php echo htmlspecialchars(formatUnitName($uname)); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <select id="filterStatus" class="form-select w-auto" onchange="loadLists(1)">
                <option value="">Tümü (Durum)</option>
                <option value="draft">Taslak</option>
                <option value="completed">Tamamlandı</option>
            </select>

            <div class="input-group w-auto" style="min-width: 240px;">
                <span class="input-group-text bg-white border-end-0"><i
                        class="fas fa-calendar-alt text-muted"></i></span>
                <input type="text" id="filterDate" class="form-control border-start-0 ps-0"
                    placeholder="Tarih aralığı seçiniz...">
                <button class="btn btn-outline-secondary" type="button" onclick="clearFilters()"
                    title="Filtreyi Temizle">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function clearFilters() {
        if (document.getElementById('filterDate')._flatpickr)
            document.getElementById('filterDate')._flatpickr.clear();

        document.getElementById('filterStatus').value = '';

        if (document.getElementById('filterUser'))
            document.getElementById('filterUser').value = '';

        loadLists(1);
    }
</script>