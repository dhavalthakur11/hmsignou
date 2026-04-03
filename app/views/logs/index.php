<?php 
/** Audit log viewer */ 
?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= base_url('logs/index') ?>" class="filter-form">
            <div class="filter-row">
                <div class="form-group mb-0">
                    <label>Action</label>
                    <select name="action">
                        <option value="">All Actions</option>
                        <?php foreach ($actions as $a): ?>
                        <option value="<?= htmlspecialchars($a) ?>"
                            <?= ($filters['action'] ?? '') === $a ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label>From</label>
                    <input type="date" name="date_from"
                           value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                </div>
                <div class="form-group mb-0">
                    <label>To</label>
                    <input type="date" name="date_to"
                           value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="<?= base_url('logs/index') ?>" class="btn btn-outline btn-sm">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Audit Log</h3>
        <span class="badge badge-gray"><?= count($logs) ?> entries</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($logs)): ?>
            <div class="empty-state"><div class="empty-icon">📋</div>
                <h3>No log entries found</h3></div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Action</th>
                    <th>User</th>
                    <th>Detail</th>
                    <th>IP</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
            <?php
                $isError = str_contains($log['action'], 'FAIL')
                        || str_contains($log['action'], 'ERROR');
            ?>
            <tr>
                <td><?= (int)$log['log_id'] ?></td>
                <td>
                    <span class="badge <?= $isError ? 'badge-red' : 'badge-blue' ?>">
                        <?= htmlspecialchars($log['action']) ?>
                    </span>
                </td>
                <td>
                    <?= htmlspecialchars($log['user_name']) ?>
                    <?php if ($log['role']): ?>
                    <br><small class="text-muted"><?= ucfirst($log['role']) ?></small>
                    <?php endif; ?>
                </td>
                <td style="max-width:260px;word-break:break-word">
                    <?= htmlspecialchars($log['detail'] ?? '—') ?>
                </td>
                <td><code style="font-size:.78rem"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></code></td>
                <td style="white-space:nowrap">
                    <?= date('d M Y H:i', strtotime($log['created_at'])) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>