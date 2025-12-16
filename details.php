<?php
require_once 'header.php';
require_once 'config.php';

$waybill_id = $_GET['id'] ?? 0;
if (!$waybill_id)
    die("<script>window.location='index.php';</script>");

$stmt = $pdo->prepare("
    SELECT w.*, u.username as creator_username 
    FROM waybills w 
    LEFT JOIN users u ON w.user_id = u.id 
    WHERE w.id = ?
");
$stmt->execute([$waybill_id]);
$waybill = $stmt->fetch();
if (!$waybill)
    die("Liste bulunamadı.");

$isDraft = ($waybill['status'] === 'draft');
$isEditable = ($isDraft || isAdmin());
?>

<!-- Include Components -->
<?php require_once 'templates/details_summary.php'; ?>
<?php require_once 'templates/shipment_list.php'; ?>
<?php require_once 'templates/shipment_form_modal.php'; ?>

<!-- Initializing JS Variables -->
<script>
    const waybillId = <?php echo $waybill_id; ?>;
    const isDraft = <?php echo $isDraft ? 'true' : 'false'; ?>;
    const isEditable = <?php echo $isEditable ? 'true' : 'false'; ?>;
</script>

<!-- Main Logic -->
<script src="js/details.js"></script>

<?php require_once 'footer.php'; ?>