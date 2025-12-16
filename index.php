<?php require_once 'header.php'; ?>

<?php if (isAdmin()): ?>
    <?php include 'templates/index/admin_stats.php'; ?>
<?php else: ?>
    <?php include 'templates/index/user_header.php'; ?>
    <?php include 'templates/index/filters.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const titleEl = document.querySelector('#filterDate').closest('.row').querySelector('h5');
            if (titleEl) titleEl.innerText = 'Geçmiş Kayıtlar';
        });
    </script>
<?php endif; ?>

<?php include 'templates/index/waybill_table.php'; ?>

<script>
    const isAdmin = <?php echo isAdmin() ? 'true' : 'false'; ?>;
</script>
<script src="js/index.js"></script>

<?php if (isset($_SESSION['login_success'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                icon: 'success',
                title: 'Hoş geldiniz!',
                text: '<?php echo htmlspecialchars(formatUnitName($_SESSION['display_name'] ?? "")); ?>',
                timer: 2000,
                showConfirmButton: false
            });
        });
    </script>
    <?php unset($_SESSION['login_success']); ?>
<?php endif; ?>

<?php require_once 'footer.php'; ?>