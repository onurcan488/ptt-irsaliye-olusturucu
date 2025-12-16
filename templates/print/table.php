<table>
    <thead>
        <tr>
            <th style="width: 30px;">#</th>
            <th style="width: 140px;">Barkod</th>
            <th style="width: 100px;">Evrak No</th>
            <th style="width: 110px;">Evrak Türü</th>
            <th>Alıcı / Adres</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($shipments as $index => $item): ?>
            <tr>
                <td style="text-align: center;"><?php echo $index + 1; ?></td>
                <td class="barcode-container">
                    <svg class="barcode-svg" jsbarcode-value="<?php echo escape($item['tracking_number']); ?>"
                        jsbarcode-format="CODE128" jsbarcode-width="1.3" jsbarcode-height="30" jsbarcode-fontSize="10"
                        jsbarcode-displayValue="true">
                    </svg>
                </td>
                <td style="text-align: center; font-weight: bold;"><?php echo nl2br(escape($item['document_no'])); ?>
                </td>
                <td style="text-align: center;"><?php echo escape($item['document_type']); ?></td>
                <td style="text-align: center;"><?php echo nl2br(escape($item['receiver_address'])); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div style="margin-top: 10px; font-size: 11px; text-align: right; margin-bottom: 20px;">
    <strong>Toplam Adet:</strong> <?php echo count($shipments); ?>
</div>