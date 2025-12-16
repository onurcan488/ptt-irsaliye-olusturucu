
let isEditing = false;
let modal;

document.addEventListener('DOMContentLoaded', () => {
    if (isEditable) {
        const modalEl = document.getElementById('shipmentModal');
        if (modalEl) {
            modal = new bootstrap.Modal(modalEl);
        }
    }
    loadShipments();
    setInterval(() => loadShipments(true), 5000);
});

function loadShipments(isBackground = false) {
    fetch(`api.php?action=get_shipments&waybill_id=${waybillId}`)
        .then(r => r.json())
        .then(res => {
            const tbody = document.getElementById('shipmentTableBody');
            let html = '';

            if (res.status === 'success' && res.data.length > 0) {
                res.data.forEach(item => {
                    let actions = '';
                    if (isEditable) {
                        actions = `
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary me-1" onclick='editShipment(${JSON.stringify(item)})'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteShipment(${item.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                    }

                    html += `
                        <tr>
                            <td class="ps-4">
                                <svg class="barcode"
                                    jsbarcode-value="${item.tracking_number}"
                                    jsbarcode-format="CODE128"
                                    jsbarcode-width="1.5"
                                    jsbarcode-height="40"
                                    jsbarcode-fontSize="12"
                                    jsbarcode-displayValue="true">
                                </svg>
                            </td>
                            <td class="fw-bold">${item.document_no}</td>
                            <td><span class="badge bg-secondary">${item.document_type}</span></td>
                            <td class="text-truncate" style="max-width: 300px;">${item.receiver_address}</td>
                            ${actions}
                        </tr>
                    `;
                });
            } else {
                const cols = isEditable ? 5 : 4;
                html = `<tr><td colspan="${cols}" class="text-center py-4 text-muted">Bu listede henüz gönderi yok.</td></tr>`;
            }
            tbody.innerHTML = html;
            try { JsBarcode(".barcode").init(); } catch (e) { }
        });
}

function openAddModal() {
    isEditing = false;
    document.getElementById('modalTitle').innerText = 'Yeni Gönderi Ekle';
    document.getElementById('shipmentForm').reset();
    document.getElementById('shipmentId').value = '';
    modal.show();
}

function editShipment(item) {
    isEditing = true;
    document.getElementById('modalTitle').innerText = 'Gönderi Düzenle';
    document.getElementById('shipmentId').value = item.id;
    document.getElementById('inputDocumentNo').value = item.document_no;
    document.getElementById('inputDocumentType').value = item.document_type;
    document.getElementById('inputTrackingNumber').value = item.tracking_number;
    document.getElementById('inputAddress').value = item.receiver_address;
    modal.show();
}

function saveShipment() {
    const trackingInput = document.getElementById('inputTrackingNumber');
    const addressInput = document.getElementById('inputAddress');
    const documentNoInput = document.getElementById('inputDocumentNo');

    // Doğrulama: Evrak No zorunlu
    if (!documentNoInput.value.trim()) {
        Swal.fire('Hata', 'Evrak No girmek zorunludur.', 'error');
        return;
    }

    // Doğrulama: Barkod 13 haneli olmalı
    const trackingVal = trackingInput.value.trim();
    if (trackingVal.length !== 13) {
        Swal.fire('Hata', 'Barkod numarası tam olarak 13 haneli olmalıdır.', 'error');
        return;
    }

    // Doğrulama: Adres zorunlu
    if (!addressInput.value.trim()) {
        Swal.fire('Hata', 'Adres alanı boş bırakılamaz.', 'error');
        return;
    }

    const form = document.getElementById('shipmentForm');
    const formData = new FormData(form);
    const action = isEditing ? 'update_shipment' : 'add_shipment';

    fetch(`api.php?action=${action}`, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                modal.hide();
                loadShipments();
                if (!isEditing) form.reset();
                Swal.fire({ icon: 'success', title: isEditing ? 'Güncellendi' : 'Eklendi', timer: 1000, showConfirmButton: false });
            } else { Swal.fire('Hata', res.message, 'error'); }
        });
}

function deleteShipment(id) {
    Swal.fire({
        title: 'Silinsin mi?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Evet'
    }).then((r) => {
        if (r.isConfirmed) {
            const fd = new FormData(); fd.append('id', id);
            fetch('api.php?action=delete_shipment', { method: 'POST', body: fd })
                .then(res => res.json()).then(res => {
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Silindi', timer: 1000, showConfirmButton: false });
                        loadShipments();
                    }
                });
        }
    });
}

function completeWaybill() {
    // Gönderi kontrolü
    const tbody = document.getElementById('shipmentTableBody');
    if (!tbody || tbody.rows.length === 0 || (tbody.rows.length === 1 && tbody.innerText.includes('henüz gönderi yok'))) {
        Swal.fire('Uyarı', 'İrsaliyeyi tamamlamak için en az 1 adet gönderi eklemelisiniz.', 'warning');
        return;
    }

    // Tarihi JS ile almak yerine PHP'den de alabiliriz ama JS yeterli.
    const today = new Date();
    const dateStr = today.getDate().toString().padStart(2, '0') + '.' + (today.getMonth() + 1).toString().padStart(2, '0') + '.' + today.getFullYear();

    Swal.fire({
        title: 'İrsaliye Tamamlanacak',
        html: `
            <div class="text-start mb-3">
                <label class="form-label fw-bold">İrsaliye Adı / Başlığı</label>
                <input type="text" id="swal-title" class="form-control" value="${dateStr} İrsaliyesi">
            </div>
            <div class="text-start mb-3">
                <label class="form-label">Listeyi Oluşturan</label>
                <textarea id="swal-prepared" class="form-control" placeholder="Ad-Soyad&#10;Sicil&#10;Ünvan" rows="4" maxlength="100"></textarea>
                <div class="form-text text-muted small">Maksimum 100 karakter.</div>
            </div>
            <div class="text-start mb-3">
                <label class="form-label">Teslim Eden</label>
                <textarea id="swal-delivered" class="form-control" placeholder="Ad-Soyad&#10;Sicil&#10;Ünvan" rows="4" maxlength="100"></textarea>
                <div class="form-text text-muted small">Maksimum 100 karakter.</div>
            </div>
            <div class="text-start mb-0">
                <label class="form-label">Teslim Alan</label>
                <textarea id="swal-received" class="form-control" placeholder="Ad-Soyad&#10;Sicil&#10;Ünvan" rows="4" maxlength="100"></textarea>
                <div class="form-text text-muted small">Maksimum 100 karakter.</div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Tamamla ve Kaydet',
        cancelButtonText: 'Vazgeç',
        confirmButtonColor: '#198754',
        focusConfirm: false,
        preConfirm: () => {
            return {
                title: document.getElementById('swal-title').value,
                prepared_by: document.getElementById('swal-prepared').value,
                delivered_by: document.getElementById('swal-delivered').value,
                received_by: document.getElementById('swal-received').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const data = result.value;
            const fd = new FormData();
            fd.append('id', waybillId);
            fd.append('title', data.title);
            fd.append('prepared_by', data.prepared_by);
            fd.append('delivered_by', data.delivered_by);
            fd.append('received_by', data.received_by);

            fetch('api.php?action=complete_list', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        Swal.fire('Tamamlandı!', 'İrsaliye başarıyla kaydedildi.', 'success')
                            .then(() => window.location = 'index.php');
                    } else {
                        Swal.fire('Hata', res.message, 'error');
                    }
                });
        }
    });
}

function printWaybill(id) {
    let oldIframe = document.getElementById('printFrame');
    if (oldIframe) oldIframe.remove();

    let iframe = document.createElement('iframe');
    iframe.id = 'printFrame';
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';

    iframe.onload = function () {
        setTimeout(function () {
            iframe.contentWindow.print();
        }, 500);
    };

    iframe.src = 'print.php?id=' + id;
    document.body.appendChild(iframe);
}

function printPTTLetter(id) {
    let oldIframe = document.getElementById('printFrame');
    if (oldIframe) oldIframe.remove();

    let iframe = document.createElement('iframe');
    iframe.id = 'printFrame';
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';

    iframe.onload = function () {
        setTimeout(function () {
            iframe.contentWindow.print();
        }, 500);
    };

    iframe.src = 'ptt_letter.php?id=' + id;
    document.body.appendChild(iframe);
}

function downloadPDF(id, type) {
    Swal.fire({
        title: 'PDF İndirme',
        text: 'PDF olarak kaydetmek için açılan pencerede Yazıcı/Hedef kısmından "PDF Olarak Kaydet" seçeneğini seçiniz.',
        icon: 'info',
        confirmButtonText: 'Anladım',
        timer: 3000,
        timerProgressBar: true
    }).then(() => {
        if (type === 'waybill') {
            printWaybill(id);
        } else {
            printPTTLetter(id);
        }
    });
}
