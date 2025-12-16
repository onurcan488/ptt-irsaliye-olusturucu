let currentPage = 1;

let calendarInstance = null;
let highlightedDates = [];

document.addEventListener('DOMContentLoaded', () => {
    loadLists(1);
    // Takvimi güncelle ve başlat
    updateCalendar();

    if (isAdmin) {
        loadStats();
        // İstatistikleri her 5 saniyede bir güncelle
        setInterval(loadStats, 5000);
    }

    // Listeyi her 5 saniyede bir güncelle
    setInterval(() => loadLists(currentPage, true), 5000);
});

function updateCalendar() {
    fetch('api.php?action=get_waybill_dates')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                highlightedDates = res.data; // ["2025-12-15", "2023-01-01"]

                if (calendarInstance) {
                    calendarInstance.redraw();
                } else {
                    initCalendar();
                }
            }
        });
}

function initCalendar() {
    calendarInstance = flatpickr("#filterDate", {
        mode: "range",
        locale: "tr",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d.m.Y",
        onChange: function (selectedDates, dateStr, instance) {
            loadLists(1);
        },
        onDayCreate: function (dObj, dStr, fp, dayElem) {
            // Construct YYYY-MM-DD for the day element
            const year = dayElem.dateObj.getFullYear();
            const month = String(dayElem.dateObj.getMonth() + 1).padStart(2, '0');
            const day = String(dayElem.dateObj.getDate()).padStart(2, '0');
            const dateStrComp = `${year}-${month}-${day}`;

            if (highlightedDates.includes(dateStrComp)) {
                dayElem.classList.add('bg-success', 'text-white', 'fw-bold', 'rounded-circle', 'shadow-sm');
                dayElem.style.backgroundColor = '#d1e7dd';
                dayElem.style.color = '#0f5132';
                dayElem.style.border = '1px solid #badbcc';
            }
        }
    });
}

function loadStats() {
    if (!isAdmin) return;
    fetch('api.php?action=get_dashboard_stats')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                const elUsers = document.getElementById('statUsers');
                const elWaybills = document.getElementById('statWaybills');
                const elShipments = document.getElementById('statShipments');
                const elCompleted = document.getElementById('statCompleted');

                if (elUsers) elUsers.innerText = res.data.total_users;
                if (elWaybills) elWaybills.innerText = res.data.total_waybills;
                if (elShipments) elShipments.innerText = res.data.total_shipments;
                if (elCompleted) elCompleted.innerText = res.data.completed_waybills;
            }
        })
        .catch(err => console.error("Stats update failed", err));
}

function loadLists(page, isBackground = false) {
    currentPage = page;
    const dateInput = document.getElementById('filterDate');
    const dateVal = dateInput ? dateInput.value : '';

    // Status Filter
    const statusInput = document.getElementById('filterStatus');
    const statusVal = statusInput ? statusInput.value : '';

    // User Filter
    const userInput = document.getElementById('filterUser');
    const userVal = userInput ? userInput.value : '';

    const tbody = document.getElementById('waybillTableBody');
    if (!isBackground) {
        tbody.innerHTML = '<tr><td colspan="' + (isAdmin ? 6 : 5) + '" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>';
    }

    fetch(`api.php?action=get_lists&page=${page}&limit=10&date=${dateVal}&status=${statusVal}&user=${userVal}`)
        .then(r => r.json())
        .then(res => {
            tbody.innerHTML = '';
            if (res.status === 'success' && res.data.items.length > 0) {
                res.data.items.forEach(item => {
                    const date = new Date(item.created_at).toLocaleDateString('tr-TR', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    let statusBadge = item.status === 'draft' ?
                        '<span class="badge bg-warning text-dark"><i class="fas fa-pen-nib me-1"></i>Taslak</span>' :
                        '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Tamamlandı</span>';

                    // Admin Column
                    let creatorCol = '';
                    if (isAdmin) {
                        creatorCol = `<td class="text-muted small fw-bold"><i class="fas fa-user-shield me-1"></i>${item.creator_name || 'Bilinmiyor'}</td>`;
                    }

                    tbody.innerHTML += `
                        <tr>
                            <td class="ps-4">${statusBadge}</td>
                            <td class="fw-bold">${item.title}</td>
                            ${creatorCol}
                            <td class="text-muted small">${date}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">${item.item_count}</span></td>
                            <td class="text-end pe-4">
                                <a href="details.php?id=${item.id}" class="btn btn-sm btn-outline-primary fw-bold">
                                    ${item.status === 'draft' ? '<i class="fas fa-edit me-1"></i> Düzenle' : '<i class="fas fa-eye me-1"></i> Görüntüle'}
                                </a>
                                ${(isAdmin || item.status === 'draft') ? `<button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteList(${item.id})">
                                    <i class="fas fa-trash"></i>
                                </button>` : ''}
                            </td>
                        </tr>
                    `;
                });

                renderPagination(res.data.pagination);
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="${isAdmin ? 6 : 5}" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 text-secondary"></i><br>
                            Henüz kayıt bulunamadı.
                        </td>
                    </tr>`;
                document.getElementById('paginationControls').innerHTML = '';
                if (document.getElementById('paginationInfo')) document.getElementById('paginationInfo').innerHTML = '';
            }
        });
}

function renderPagination(pagination) {
    const container = document.getElementById('paginationControls');
    const infoContainer = document.getElementById('paginationInfo'); // Yeni bilgi alanı

    container.innerHTML = '';
    if (infoContainer) infoContainer.innerHTML = `<span class="text-muted small">Toplam <b>${pagination.total_items}</b> kayıt, Sayfa <b>${pagination.current_page}</b> / ${pagination.total_pages}</span>`;

    if (pagination.total_pages <= 1) return;

    let html = '';

    // Önceki
    html += `<li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="loadLists(${pagination.current_page - 1})">Önceki</a>
                 </li>`;

    // Sayfalar: Hepsini göstermek yerine akıllı gösterim yapılabilir ama şimdilik basit tutalım
    // Ancak çok sayfa varsa hepsini göstermek taşmaya neden olur. 
    // Basit bir mantık: İlk, Son, Mevcut ve çevresi.

    const total = pagination.total_pages;
    const current = pagination.current_page;

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
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        } else {
            html += `<li class="page-item ${current === p ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0)" onclick="loadLists(${p})">${p}</a>
                         </li>`;
        }
    });

    // Sonraki
    html += `<li class="page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="loadLists(${pagination.current_page + 1})">Sonraki</a>
                 </li>`;

    container.innerHTML = html;
}

function createNewDraft() {
    fetch('api.php?action=create_draft', { method: 'POST' })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                window.location.href = `details.php?id=${res.data.id}&new=true`;
            } else {
                Swal.fire('Hata', res.message, 'error');
            }
        });
}

function deleteList(id) {
    event.preventDefault();
    event.stopPropagation();
    Swal.fire({
        title: 'Silinsin mi?',
        text: 'Bu liste ve içindeki gönderiler silinecek.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, Sil',
        cancelButtonText: 'İptal',
        confirmButtonColor: '#d33'
    }).then(r => {
        if (r.isConfirmed) {
            const fd = new FormData();
            fd.append('id', id);
            fetch('api.php?action=delete_list', { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Silindi', timer: 1000, showConfirmButton: false });
                        loadLists(currentPage);
                        updateCalendar();
                        if (isAdmin) loadStats();
                    }
                });
        }
    });
}
