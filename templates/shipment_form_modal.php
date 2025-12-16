<?php if ($isEditable): ?>
    <div class="modal fade" id="shipmentModal" tabindex="-1" aria-labelledby="modalTitle" aria-modal="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitle">Gönderi Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="shipmentForm">
                        <input type="hidden" name="waybill_id" value="<?php echo $waybill_id; ?>">
                        <input type="hidden" name="id" id="shipmentId">

                        <div class="mb-3">
                            <label class="form-label">Evrak No</label>
                            <textarea class="form-control" name="document_no" id="inputDocumentNo" rows="2" required
                                maxlength="70"></textarea>
                            <div class="form-text text-danger">Evrak No zorunludur. (Maks: 70 karakter)</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Evrak Türü</label>
                            <select class="form-select" name="document_type" id="inputDocumentType">
                                <?php
                                $creatorUsername = $waybill['creator_username'] ?? '';

                                if ($creatorUsername === 'bassavcilik'):
                                    ?>
                                    <option value="MUHABERE">MUHABERE</option>
                                    <option value="TALİMAT">TALİMAT</option>
                                    <option value="YAKALAMA">YAKALAMA</option>
                                    <option value="İLAMAT">İLAMAT</option>
                                    <option value="SORUŞTURMA">SORUŞTURMA</option>
                                    <option value="EMANET">EMANET</option>
                                    <option value="BAKANLIK MUHABERE">BAKANLIK MUHABERE</option>
                                <?php elseif ($creatorUsername === 'icramudurlugu'): ?>
                                    <option value="MUHABERE">MUHABERE</option>
                                    <option value="TALİMAT">TALİMAT</option>
                                    <option value="ESAS">ESAS</option>
                                <?php else: ?>
                                    <option value="ESAS">ESAS</option>
                                    <option value="MUHABERE">MUHABERE</option>
                                    <option value="TALİMAT">TALİMAT</option>
                                    <option value="SORGU">SORGU</option>
                                    <option value="DEĞİŞİK İŞ">DEĞİŞİK İŞ</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Barkod Numarası</label>
                            <input type="text" class="form-control" name="tracking_number" id="inputTrackingNumber" required
                                maxlength="13">
                            <div class="form-text text-danger">Barkod 13 haneli olmalıdır (Örn: RR123456789TR).</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adres</label>
                            <textarea class="form-control" name="receiver_address" id="inputAddress" rows="3" required
                                maxlength="250"></textarea>
                            <div class="form-text text-danger">Adres zorunludur. (Maks: 250 karakter)</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveShipment()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>