<?php require_once 'header.php';
require_once 'config.php'; ?>

<?php include 'templates/search/form.php'; ?>

<script>
    const isAdmin = <?php echo isAdmin() ? 'true' : 'false'; ?>;
</script>
<script src="js/search.js"></script>

<?php require_once 'footer.php'; ?>