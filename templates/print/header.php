<div class="header">
    <div style="margin-top: 20px;">
        <h2 style="margin: 0 0 5px 0; font-size: 24px; font-weight: bold;">T.C.</h2>
        <h2 style="margin: 0 0 20px 0; font-size: 24px; font-weight: bold;">
            <?php echo mb_strtoupper(str_replace('i', 'İ', $waybill['display_name']), 'UTF-8'); ?>
        </h2>
    </div>
    <h1
        style="border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 10px 0; margin: 20px 0; font-size: 20px;">
        POSTA İRSALİYESİ</h1>
    <div class="meta">
        <span><strong>Liste:</strong> <?php echo escape($waybill['title']); ?></span>
        <span><strong>Tarih:</strong>
            <?php echo date('d.m.Y H:i', strtotime($waybill['completed_at'] ?? 'now')); ?></span>
    </div>
</div>