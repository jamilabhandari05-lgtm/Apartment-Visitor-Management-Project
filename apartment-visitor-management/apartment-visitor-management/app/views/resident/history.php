<?php $pageTitle = 'My Visitor History'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<main class="page">
    <h1>My Visitor History</h1>
    <p class="page-sub">Every visitor logged in at my flat.</p>

    <div class="card">
        <?php if (empty($logs)): ?>
            <p class="empty-state">No visitors logged yet.</p>
        <?php else: ?>
        <div class="table-scroll">
        <table class="data-table">
            <tr><th>Visitor</th><th>Purpose</th><th>Type</th><th>Checked in</th><th>Checked out</th><th>Status</th></tr>
            <?php foreach ($logs as $row): $over = VisitorLog::isOverstaying($row); ?>
                <tr class="<?= $over ? 'row-overstay' : '' ?>">
                    <td><?= e($row['visitor_name']) ?></td>
                    <td><?= e($row['purpose'] ?? '-') ?></td>
                    <td><?= $row['gate_pass_id'] ? 'Pre-approved' : 'Walk-in' ?></td>
                    <td><?= format_datetime($row['checked_in_at']) ?></td>
                    <td><?= $row['checked_out_at'] ? format_datetime($row['checked_out_at']) : '-' ?></td>
                    <td>
                        <?php if (!$row['checked_out_at'] && $over): ?><span class="badge badge-overstay">Overstaying</span>
                        <?php elseif (!$row['checked_out_at']): ?><span class="badge badge-inside">Inside</span>
                        <?php else: ?><span class="badge badge-checked_out">Checked out</span><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        </div>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
