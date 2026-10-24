<?php $pageTitle = 'Visitor Log & Reports'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<main class="page">
    <h1>Visitor Log &amp; Reports</h1>
    <p class="page-sub">Full gate log across every flat, for audit and oversight.</p>

    <div class="card">
        <form method="GET" action="/index.php" class="filter-bar">
            <input type="hidden" name="route" value="admin/visitor-log">
            <label>From <input type="date" name="date_from" value="<?= e($_GET['date_from'] ?? '') ?>"></label>
            <label>To <input type="date" name="date_to" value="<?= e($_GET['date_to'] ?? '') ?>"></label>
            <label>Flat
                <select name="flat_id">
                    <option value="">All flats</option>
                    <?php foreach ($flats as $flat): ?>
                        <option value="<?= (int)$flat['id'] ?>" <?= (($_GET['flat_id'] ?? '') == $flat['id']) ? 'selected' : '' ?>>
                            <?= e($flat['block'] . '-' . $flat['number']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a class="btn btn-ghost" href="/index.php?route=admin/visitor-log">Reset</a>
        </form>
    </div>

    <div class="card">
        <div class="card-head"><h2>Gate log (<?= count($logs) ?>)</h2></div>
        <?php if (empty($logs)): ?>
            <p class="empty-state">No visitor entries match this filter.</p>
        <?php else: ?>
        <div class="table-scroll">
        <table class="data-table">
            <tr><th>Flat</th><th>Visitor</th><th>Purpose</th><th>Type</th><th>Checked in</th><th>Checked out</th><th>Guard</th><th>Status</th></tr>
            <?php foreach ($logs as $row): $over = VisitorLog::isOverstaying($row); ?>
                <tr class="<?= $over ? 'row-overstay' : '' ?>">
                    <td><?= e($row['block'] . '-' . $row['number']) ?></td>
                    <td><?= e($row['visitor_name']) ?><?php if (!empty($row['visitor_phone'])): ?><br><span class="muted"><?= e($row['visitor_phone']) ?></span><?php endif; ?></td>
                    <td><?= e($row['purpose'] ?? '-') ?></td>
                    <td><?= $row['gate_pass_id'] ? 'Pre-approved' : 'Walk-in' ?></td>
                    <td><?= format_datetime($row['checked_in_at']) ?></td>
                    <td><?= $row['checked_out_at'] ? format_datetime($row['checked_out_at']) : '-' ?></td>
                    <td><?= e($row['checked_in_by_name']) ?></td>
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

    <div class="card">
        <div class="card-head"><h2>Frequent visitors</h2></div>
        <?php if (empty($frequentVisitors)): ?>
            <p class="empty-state">Not enough repeat visits yet to surface a frequent-visitor list.</p>
        <?php else: ?>
        <div class="table-scroll">
        <table class="data-table">
            <tr><th>Visitor</th><th>Phone</th><th>Visits</th><th>Last visit</th></tr>
            <?php foreach ($frequentVisitors as $fv): ?>
                <tr>
                    <td><?= e($fv['visitor_name']) ?></td>
                    <td><?= e($fv['visitor_phone']) ?></td>
                    <td><?= (int)$fv['visit_count'] ?></td>
                    <td><?= format_datetime($fv['last_visit']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        </div>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
