<?php
require_once 'header.php';
require_once 'config.php';

if (!isAdmin()) {
    echo "<script>Swal.fire('Yetkisiz Erişim', 'Bu sayfaya erişim yetkiniz yok.', 'error').then(() => window.location = 'index.php');</script>";
    exit;
}
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="mb-0 fw-bold"><i class="fas fa-users me-2" style="color: #6f42c1;"></i>Kullanıcı Yönetimi</h2>
        <p class="text-muted mb-0">Sistemdeki kullanıcıları buradan yönetebilirsiniz.</p>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary shadow-sm" onclick="openUserModal()">
            <i class="fas fa-user-plus me-2"></i> Yeni Kullanıcı
        </button>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0"
                        placeholder="Kullanıcı ara..." onkeyup="searchUsers()">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Kullanıcı Adı</th>
                        <th>Görünen Ad</th>
                        <th>Rol</th>
                        <th>Kayıt Tarihi</th>
                        <th class="text-end pe-4">İşlemler</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="spinner-border text-primary"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-between align-items-center">
        <div id="paginationInfo" class="mb-0"></div>
        <nav>
            <ul class="pagination justify-content-center mb-0" id="paginationControls"></ul>
        </nav>
    </div>
</div>

<!-- Kullanıcı Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Kullanıcı Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" name="id" id="userId">
                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" name="username" id="inputUsername" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Görünen Ad (Birim Adı)</label>
                        <input type="text" class="form-control" name="display_name" id="inputDisplayName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Şifre</label>
                        <input type="password" class="form-control" name="password" id="inputPassword"
                            placeholder="Sadece değiştirmek için girin">
                        <div class="form-text">Yeni kullanıcı eklerken zorunludur.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="role" id="inputRole">
                            <option value="user">Kullanıcı</option>
                            <option value="admin">Yönetici</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">Kaydet</button>
            </div>
        </div>
    </div>
</div>

<script>
    let userModal;
    let searchTimeout;
    let currentPage = 1;

    document.addEventListener('DOMContentLoaded', () => {
        userModal = new bootstrap.Modal(document.getElementById('userModal'));
        loadUsers(1);
        setInterval(() => loadUsers(currentPage, true), 5000);
    });

    function searchUsers() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadUsers(1);
        }, 500);
    }

    function loadUsers(page, isBackground = false) {
        currentPage = page;
        const search = document.getElementById('searchInput').value;
        const tbody = document.getElementById('usersTableBody');
        if (!isBackground) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>';
        }

        fetch(`api.php?action=get_users&page=${page}&limit=10&search=${encodeURIComponent(search)}`)
            .then(r => r.json())
            .then(res => {
                tbody.innerHTML = '';
                if (res.status === 'success' && res.data.items.length > 0) {
                    res.data.items.forEach(user => {
                        const date = new Date(user.created_at).toLocaleDateString('tr-TR');
                        tbody.innerHTML += `
                        <tr>
                            <td class="ps-4 fw-bold">${user.username}</td>
                            <td>${user.display_name}</td>
                            <td>
                                <span class="badge ${user.role === 'admin' ? 'bg-danger' : 'bg-primary'}">
                                    ${user.role === 'admin' ? 'Yönetici' : 'Kullanıcı'}
                                </span>
                            </td>
                            <td class="small text-muted">${date}</td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary me-1" onclick='editUser(${JSON.stringify(user)})'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${user.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    });
                    renderPagination(res.data.pagination);
                } else {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">Kullanıcı bulunamadı.</td></tr>`;
                    document.getElementById('paginationControls').innerHTML = '';
                }
            });
    }

    function renderPagination(pagination) {
        const container = document.getElementById('paginationControls');
        const infoContainer = document.getElementById('paginationInfo');

        container.innerHTML = '';
        if (infoContainer) {
            infoContainer.innerHTML = `<span class="text-muted small">Toplam <b>${pagination.total_items}</b> kayıt, Sayfa <b>${pagination.current_page}</b> / ${pagination.total_pages}</span>`;
        }

        if (pagination.total_pages <= 1) return;

        let html = '';
        const total = pagination.total_pages;
        const current = pagination.current_page;

        // Önceki
        html += `<li class="page-item ${current === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="loadUsers(${current - 1})">Önceki</a>
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
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            } else {
                html += `<li class="page-item ${current === p ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0)" onclick="loadUsers(${p})">${p}</a>
                         </li>`;
            }
        });

        // Sonraki
        html += `<li class="page-item ${current === total ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="loadUsers(${current + 1})">Sonraki</a>
                 </li>`;

        container.innerHTML = html;
    }

    function openUserModal() {
        document.getElementById('modalTitle').innerText = 'Yeni Kullanıcı Ekle';
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('inputPassword').required = true;
        userModal.show();
    }

    function editUser(user) {
        document.getElementById('modalTitle').innerText = 'Kullanıcı Düzenle';
        document.getElementById('userId').value = user.id;
        document.getElementById('inputUsername').value = user.username;
        document.getElementById('inputDisplayName').value = user.display_name;
        document.getElementById('inputRole').value = user.role;
        document.getElementById('inputPassword').required = false;
        document.getElementById('inputPassword').value = '';
        userModal.show();
    }

    function saveUser() {
        const form = document.getElementById('userForm');
        const formData = new FormData(form);
        const id = document.getElementById('userId').value;
        const action = id ? 'update_user' : 'add_user';

        fetch(`api.php?action=${action}`, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    userModal.hide();
                    loadUsers(currentPage);
                    Swal.fire({ icon: 'success', title: 'Kaydedildi', timer: 1000, showConfirmButton: false });
                } else {
                    Swal.fire('Hata', res.message, 'error');
                }
            });
    }

    function deleteUser(id) {
        Swal.fire({
            title: 'Silinsin mi?',
            text: 'Bu kullanıcı ve tüm irsaliyeleri silinecek!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Evet, Sil'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('id', id);
                fetch('api.php?action=delete_user', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'success') {
                            loadUsers(currentPage);
                            Swal.fire({ icon: 'success', title: 'Silindi', timer: 1000, showConfirmButton: false });
                        } else {
                            Swal.fire('Hata', res.message, 'error');
                        }
                    });
            }
        });
    }
</script>

<?php require_once 'footer.php'; ?>