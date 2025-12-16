<div class="signatures">
    <div class="sig-block">
        <span class="sig-title">LİSTEYİ OLUŞTURAN</span>
        <div class="sig-name"><?php echo nl2br(escape($waybill['prepared_by'])); ?></div>
    </div>
    <div class="sig-block">
        <span class="sig-title">TESLİM EDEN</span>
        <div class="sig-name"><?php echo nl2br(escape($waybill['delivered_by'])); ?></div>
    </div>
    <div class="sig-block">
        <span class="sig-title">TESLİM ALAN</span>
        <div class="sig-name"><?php echo nl2br(escape($waybill['received_by'])); ?></div>
    </div>
</div>