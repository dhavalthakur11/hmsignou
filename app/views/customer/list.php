<?php
/**
 * Customer List View — Admin/Receptionist use
 */
?>

<div class="card">
    <div class="card-header">
        <h3>Registered Customers</h3>
        <span class="badge badge-gray"><?= count($customers) ?> total</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($customers)): ?>
            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <h3>No customers yet</h3>
                <p>Customers will appear here after they register.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Registered</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($customers as $c): ?>
            <tr>
                <td><?= (int)$c['user_id'] ?></td>
                <td>
                    <div class="guest-cell">
                        <span class="guest-avatar">
                            <?= strtoupper(substr($c['name'], 0, 1)) ?>
                        </span>
                        <strong><?= htmlspecialchars($c['name']) ?></strong>
                    </div>
                </td>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                <td>
                    <?= $c['created_at']
                        ? date('d M Y', strtotime($c['created_at']))
                        : '—' ?>
                </td>
                <td>
                    <span class="badge <?= $c['is_active'] ? 'badge-green' : 'badge-red' ?>">
                        <?= $c['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="<?= base_url('booking/index?search=' . urlencode($c['name'])) ?>"
                           class="btn btn-outline btn-sm">
                            View Bookings
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>