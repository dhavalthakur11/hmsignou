<?php
/**
 * Billing List View
 */

$bills   = $bills ?? [];
$filters = $filters ?? [];
?>

<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
        <div>
            <h3>Billing</h3>
            <p class="text-muted" style="margin:4px 0 0;">Manage invoices and payment records</p>
        </div>
        <span class="badge badge-blue"><?= count($bills) ?> total</span>
    </div>

    <div class="card-body">
        <form method="GET" action="<?= base_url('billing/index') ?>" class="filters-form" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
            <div class="form-group" style="min-width:220px;">
                <label for="payment_status">Payment Status</label>
                <select name="payment_status" id="payment_status">
                    <option value="">All</option>
                    <option value="paid" <?= (($filters['payment_status'] ?? '') === 'paid') ? 'selected' : '' ?>>Paid</option>
                    <option value="unpaid" <?= (($filters['payment_status'] ?? '') === 'unpaid') ? 'selected' : '' ?>>Unpaid</option>
                    <option value="partial" <?= (($filters['payment_status'] ?? '') === 'partial') ? 'selected' : '' ?>>Partial</option>
                </select>
            </div>

            <div class="form-group" style="display:flex;align-items:flex-end;gap:8px;">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="<?= base_url('billing/index') ?>" class="btn btn-outline">Reset</a>
            </div>
        </form>

        <?php if (empty($bills)): ?>
            <div class="empty-state" style="padding:24px;">
                <div class="empty-icon">🧾</div>
                <h3>No bills found</h3>
                <p>No invoice records matched your filter.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Bill ID</th>
                            <th>Booking ID</th>
                            <th>Total Amount</th>
                            <th>Payment Status</th>
                            <th>Created</th>
                            <th>Paid At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bills as $bill): ?>
                            <?php
                            $status = strtolower($bill['payment_status'] ?? 'unpaid');
                            $badgeClass = match ($status) {
                                'paid'    => 'badge-green',
                                'partial' => 'badge-orange',
                                default   => 'badge-red',
                            };
                            ?>
                            <tr>
                                <td>
                                    <strong>INV-<?= str_pad((string)($bill['bill_id'] ?? 0), 6, '0', STR_PAD_LEFT) ?></strong>
                                </td>
                                <td>#<?= (int)($bill['booking_id'] ?? 0) ?></td>
                                <td>₹<?= number_format((float)($bill['total_amount'] ?? 0), 2) ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= !empty($bill['created_at']) ? date('d M Y', strtotime($bill['created_at'])) : '-' ?>
                                </td>
                                <td>
                                    <?= !empty($bill['paid_at']) ? date('d M Y', strtotime($bill['paid_at'])) : '-' ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('billing/invoice/' . (int)($bill['booking_id'] ?? 0)) ?>"
                                       class="btn btn-outline btn-sm">
                                        View Invoice
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>