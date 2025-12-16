let currentSearchTerm = '';
let currentPage = 1;
// isAdmin variable is defined in the main PHP file context before including this script, 
// but to be safe we can re-inject it or assume it's global if defined in the main file's script block.
// However, it's better if this JS file is pure JS and doesn't rely on inline PHP.
// The main file should set window.isAdmin = ...

function search(page = 1) {
    const termInput = document.getElementById('searchTerm');
    const term = termInput ? termInput.value.trim() : currentSearchTerm;

    if (term.length < 3) {
        Swal.fire('Uyarı', 'Lütfen en az 3 karakter giriniz.', 'warning');
        return;
    }

    currentSearchTerm = term;
    currentPage = page;

    const resultDiv = document.getElementById('resultsArea');
    const paginationDiv = document.getElementById('paginationArea');

    if (page === 1) {
        resultDiv.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Aranıyor...</p></div>';
        paginationDiv.innerHTML = '';
    }

    fetch(`api.php?action=search_shipments&term=${encodeURIComponent(term)}&page=${page}&limit=10`)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                if (res.data.items.length > 0) {
                    let html = `<div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-success"><i class="fas fa-check-circle me-1"></i> ${res.data.pagination.total_items} sonuç bulundu</span>
                                <span class="small text-muted">Sayfa ${res.data.pagination.current_page} / ${res.data.pagination.total_pages}</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">`;
                    html += `<thead class="table-light"><tr>
                            <th>Barkod</th>
                            <th>Evrak No</th>
                            <th>Evrak Türü</th>
                            ${isAdmin ? '<th>Oluşturan Birim</th>' : ''}
                            <th>Liste / Durum</th>
                            <th>Tarih</th>
                            <th></th>
                        </tr></thead><tbody>`;

                    res.data.items.forEach(item => {
                        const date = new Date(item.waybill_date).toLocaleDateString('tr-TR');
                        const isDraft = item.waybill_status === 'draft';
                        const statusBadge = isDraft
                            ? '<span class="badge bg-warning text-dark ms-2"><i class="fas fa-pen-nib me-1"></i>Taslak</span>'
                            : '<span class="badge bg-success ms-2"><i class="fas fa-check me-1"></i>Tamamlandı</span>';

                        let creatorCol = '';
                        if (isAdmin) {
                            creatorCol = `<td class="small text-muted"><i class="fas fa-user-shield me-1"></i>${item.creator_name || 'Bilinmiyor'}</td>`;
                        }

                        html += `
                            <tr>
                                <td class="ps-3">
                                    <svg class="barcode"
                                        jsbarcode-value="${item.tracking_number}"
                                        jsbarcode-format="CODE128"
                                        jsbarcode-width="1.2"
                                        jsbarcode-height="30"
                                        jsbarcode-fontSize="11"
                                        jsbarcode-displayValue="true">
                                    </svg>
                                </td>
                                <td class="fw-bold">${item.document_no}</td>
                                <td><span class="badge bg-secondary">${item.document_type}</span></td>
                                ${creatorCol}
                                <td>
                                    <a href="details.php?id=${item.waybill_id}" target="_blank" class="text-decoration-none fw-bold hover-primary">
                                        ${item.waybill_title} <i class="fas fa-external-link-alt ms-1 small text-muted"></i>
                                    </a>
                                    ${statusBadge}
                                </td>
                                <td class="small text-muted">${date}</td>
                                <td class="text-end"><a href="details.php?id=${item.waybill_id}" class="btn btn-sm btn-outline-primary"><i class="fas fa-arrow-right"></i> Git</a></td>
                            </tr>
                        `;
                    });

                    html += `</tbody></table></div></div>`;
                    resultDiv.innerHTML = html;
                    try { JsBarcode(".barcode").init(); } catch (e) { }

                    renderPagination(res.data.pagination);
                } else {
                    resultDiv.innerHTML = `
                        <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                            <i class="fas fa-search-minus fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Sonuç Bulunamadı</h5>
                            <p class="text-muted small">"${term}" aramasıyla eşleşen bir kayıt yok.</p>
                        </div>
                    `;
                    paginationDiv.innerHTML = '';
                }
            } else {
                Swal.fire('Hata', res.message, 'error');
                resultDiv.innerHTML = '';
            }
        });
}

function renderPagination(pagination) {
    const container = document.getElementById('paginationArea');
    if (pagination.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '<nav><ul class="pagination justify-content-center">';
    const total = pagination.total_pages;
    const current = pagination.current_page;

    // Önceki
    html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
                <button class="page-link" onclick="search(${current - 1})">Önceki</button>
             </li>`;

    let pages = [];
    if (total <= 7) {
        for (let i = 1; i <= total; i++) pages.push(i);
    } else {
        if (current <= 4) {
            pages = [1, 2, 3, 4, 5, '...', total];
        } else if (current >= total - 3) {
            pages = [1, '...', total - 4, total - 3, total - 2, total - 1, total];
        } else {
            pages = [1, '...', current - 1, current, current + 1, '...', total];
        }
    }

    pages.forEach(p => {
        if (p === '...') {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        } else {
            html += `<li class="page-item ${current === p ? 'active' : ''}">
                        <button class="page-link" onclick="search(${p})">${p}</button>
                     </li>`;
        }
    });

    // Sonraki
    html += `<li class="page-item ${current === total ? 'disabled' : ''}">
                <button class="page-link" onclick="search(${current + 1})">Sonraki</button>
             </li>`;

    html += '</ul></nav>';
    container.innerHTML = html;
}
