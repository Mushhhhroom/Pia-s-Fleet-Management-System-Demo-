<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Non-Usage — <?= esc($cert['certificate_no']) ?></title>
    <style>
        body { font-family: "Times New Roman", Georgia, serif; font-size: 14px; color: #0f172a; margin: 0; padding: 40px; background: #fff; }
        .sheet { max-width: 820px; margin: 0 auto; border: 2px double #0f172a; padding: 46px 54px; }
        .agency { text-align: center; font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.03em; }
        .office { text-align: center; font-size: 13px; margin-bottom: 4px; }
        .republic { text-align: center; font-size: 12px; text-transform: uppercase; margin-bottom: 22px; }
        h1 { text-align: center; font-size: 24px; text-transform: uppercase; margin: 30px 0 6px; letter-spacing: 0.06em; }
        .certno { text-align: center; font-size: 13px; margin-bottom: 30px; font-family: Consolas, monospace; }
        p.body { text-align: justify; line-height: 1.9; text-indent: 40px; }
        table { width: 100%; border-collapse: collapse; margin: 24px 0; font-size: 13px; }
        th, td { border: 1px solid #334155; padding: 7px 9px; }
        th { background: #e2e8f0; text-align: left; width: 40%; text-transform: uppercase; font-size: 11px; }
        .sig { text-align: center; margin-top: 56px; }
        .sig .line { border-top: 1px solid #0f172a; width: 320px; margin: 0 auto; padding-top: 5px; }
        .actions { text-align: center; margin-bottom: 24px; }
        .actions button, .actions a { font-family: Arial, sans-serif; font-size: 12px; padding: 7px 15px; margin: 0 4px; cursor: pointer; border: 1px solid #334155; background: #f1f5f9; border-radius: 4px; text-decoration: none; color: #0f172a; }
        @media print { .actions { display: none; } .sheet { border: none; padding: 10mm; } }
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()">🖨 Print / Save as PDF</button>
        <a href="<?= base_url('compliance?month=' . urlencode($cert['period_month'])) ?>">← Back to Compliance Portal</a>
    </div>

    <div class="sheet">
        <div class="republic">Republic of the Philippines</div>
        <div class="agency">Philippine Information Agency</div>
        <div class="office">Administrative Division &bull; Motorpool Operations</div>

        <h1>Certificate of Non-Usage</h1>
        <div class="certno">Certificate No. <strong><?= esc($cert['certificate_no']) ?></strong></div>

        <p class="body">
            This is to certify that the government fleet unit described below was recorded
            <strong>zero (0) official trip assignments</strong> during the calendar month of
            <strong><?= esc(date('F Y', strtotime($cert['period_month'] . '-01'))) ?></strong>,
            and that its odometer reading did not advance beyond the recorded movement for that period.
        </p>

        <table>
            <tr><th>Vehicle Code</th><td><?= esc($cert['vehicle_code'] ?? '—') ?></td></tr>
            <tr><th>Plate Number</th><td><strong><?= esc($cert['plate_number'] ?? '—') ?></strong></td></tr>
            <tr><th>Make &amp; Model</th><td><?= esc(trim(($cert['make'] ?? '') . ' ' . ($cert['model'] ?? ''))) ?></td></tr>
            <tr><th>Fleet Category</th><td><?= esc(ucfirst($cert['fleet_category'] ?? 'pool')) ?></td></tr>
            <tr><th>Covering Period</th><td><?= esc(date('F 1 – ', strtotime($cert['period_month'] . '-01'))) . esc(date('t, Y', strtotime($cert['period_month'] . '-01'))) ?></td></tr>
            <tr><th>Recorded Trip Count</th><td><?= (int) $cert['trip_count'] ?></td></tr>
            <tr><th>Odometer — Start of Period</th><td><?= number_format((float) $cert['odometer_start'], 2) ?> km</td></tr>
            <tr><th>Odometer — End of Period</th><td><?= number_format((float) $cert['odometer_end'], 2) ?> km</td></tr>
            <tr><th>Net Odometer Movement</th><td><?= number_format((float) $cert['odometer_end'] - (float) $cert['odometer_start'], 2) ?> km</td></tr>
        </table>

        <p class="body">
            Issued pursuant to the fleet utilization reporting requirements of the Commission on Audit (COA)
            and the Human Resource Development Division (HRDD) for the month of
            <?= esc(date('F Y', strtotime($cert['period_month'] . '-01'))) ?>.
        </p>

        <div class="sig">
            <div class="line">
                <strong><?= esc($cert['generated_by_name'] ?? 'Fleet Management Officer') ?></strong><br>
                Issuing Officer &bull; Date Issued: <?= esc(date('F d, Y', strtotime($cert['created_at'] ?? 'now'))) ?>
            </div>
        </div>
    </div>
</body>
</html>
