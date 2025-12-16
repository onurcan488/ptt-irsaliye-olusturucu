<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-4">Barkod No</th>
                        <th scope="col">Evrak No</th>
                        <th scope="col">Evrak Türü</th>
                        <th scope="col">Adres</th>
                        <?php if ($isEditable): ?>
                            <th scope="col" class="text-end pe-4">İşlemler</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody id="shipmentTableBody">
                    <tr>
                        <td colspan="5" class="text-center py-4">Yükleniyor...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>