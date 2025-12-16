</div> <!-- .container closing -->

<footer class="text-center text-muted mt-5 pb-4 small">
    <div class="container">
        <hr class="mb-4">
        <p class="mb-1">Bu yazılım <strong><?php echo getSetting('institution_name') ?: 'Araklı Adliyesi'; ?></strong>
            için özel olarak geliştirilmiştir.</p>
        <p class="mb-0">
            Geliştirici: <strong>Onur Can ALEMDAROĞLU</strong> - Sicil: <strong>255867</strong> - <a
                href="mailto:ab255867@adalet.gov.tr" class="text-decoration-none text-muted">ab255867@adalet.gov.tr</a>
            <a href="https://github.com/onurcan488" target="_blank" class="text-muted ms-2" title="GitHub"><i
                    class="fab fa-github"></i></a>
            <span class="badge bg-secondary ms-2 cursor-pointer"
                style="font-size: 0.75rem; vertical-align: middle; cursor: pointer;" data-bs-toggle="modal"
                data-bs-target="#changelogModal" title="Geliştirme Notlarını Gör">
                v1.0
            </span>
        </p>
    </div>
</footer>

<!-- Changelog Modal -->
<div class="modal fade" id="changelogModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-code-branch me-2"></i>Geliştirme Notları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 60vh; overflow-y: auto;">
                <?php
                $changelogFile = 'changelog.json';
                $latestDate = '16.12.2025'; // Varsayılan tarih
                
                if (file_exists($changelogFile)) {
                    $changelog = json_decode(file_get_contents($changelogFile), true);
                    if (!empty($changelog) && isset($changelog[0]['date'])) {
                        $latestDate = $changelog[0]['date'];
                    }

                    // Assuming an escape function exists for security
                    if (!function_exists('escape')) {
                        function escape($string)
                        {
                            return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
                        }
                    }
                    foreach ($changelog as $log) {
                        echo '<div class="mb-4 border-bottom pb-3">';
                        echo '<div class="d-flex justify-content-between align-items-center mb-2">';
                        echo '<h5 class="fw-bold text-primary mb-0">' . escape($log['version']) . '</h5>';
                        echo '<span class="text-muted small"><i class="far fa-calendar-alt me-1"></i>' . escape($log['date']) . '</span>';
                        echo '</div>';
                        echo '<p class="fw-medium mb-2">' . escape($log['description'] ?? '') . '</p>';
                        echo '<ul class="list-unstyled mb-0">';
                        foreach ($log['changes'] as $change) {
                            echo '<li class="mb-1 text-muted"><i class="fas fa-check-circle text-success me-2 small"></i>' . escape($change) . '</li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="text-muted text-center">Geliştirme notları bulunamadı.</p>';
                }
                ?>
            </div>
            <div class="modal-footer bg-light p-2 justify-content-center">
                <small class="text-muted">Son Güncelleme: <?php echo $latestDate; ?></small>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Custom JS -->
<script>
    // Genel JS kodları buraya gelebilir
</script>
</body>

</html>