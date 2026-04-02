<?php
/**
 * Booking Index — full list with filters, check-in/out actions.
 */
?>

<!-- ── Filter bar (staff only) ──────────────────────────────── -->
<?php if ($is_staff): ?>
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= base_url('booking/index') ?>" class="filter-form">
            <div class="filter-row">
                <div class="form-group mb-0">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All</option>
                        <?php foreach (['confirmed','checked_in','checked_out','cancelled'] as $s): ?>
                        <option value="<?= $s ?>"
                            <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>>
                            <?= ucwords(str_replace('_',' ', $s)) ?>
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
                <div class="form-group mb-0">
                    <label>Search</label>
                    <input type="text" name="search"
                           placeholder="Guest name or room…"
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    <a href="<?= base_url('booking/index') ?>" class="btn btn-outline btn-sm">Reset</a>
                    <a href="<?= base_url('booking/create') ?>"  class="btn btn-success btn-sm">+ New Booking</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
<div class="page-actions mb-4">
    <a href="<?= base_url('booking/create') ?>" class="btn btn-primary">+ New Booking</a>
</div>
<?php endif; ?>

<!-- ── Bookings table ────────────────────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h3>
            <?= $is_staff ? 'All Bookings' : 'My Bookings' ?>
            <span class="badge badge-gray" style="margin-left:8px">
                <?= count($bookings) ?>
            </span>
        </h3>
    </div>
    <div class="card-body p-0">
        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <div class="empty-icon">📅</div>
                <h3>No bookings found</h3>
                <p>Try adjusting filters or create a new booking.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Nights</th>
                    <th>Guests</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $b):
                $nights = (new DateTime($b['check_in']))->diff(new DateTime($b['check_out']))->days;
                $statusBadge = match($b['status']) {
                    'confirmed'   => 'badge-blue',
                    'checked_in'  => 'badge-green',
                    'checked_out' => 'badge-gray',
                    'cancelled'   => 'badge-red',
                    default       => 'badge-gray',
                };
            ?>
            <tr>
                <td><strong>#<?= (int)$b['booking_id'] ?></strong></td>
                <td>
                    <div class="guest-cell">
                        <span class="guest-avatar">
                            <?= strtoupper(substr($b['guest_name'], 0, 1)) ?>
                        </span>
                        <div>
                            <div><?= htmlspecialchars($b['guest_name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($b['guest_email']) ?></small>
                        </div>
                    </div>
                </td>
                <td>
                    <strong><?= htmlspecialchars($b['room_number']) ?></strong>
                    <br><small class="text-muted"><?= htmlspecialchars($b['room_type']) ?></small>
                </td>
                <td><?= htmlspecialchars(date('d M Y', strtotime($b['check_in']))) ?></td>
                <td><?= htmlspecialchars(date('d M Y', strtotime($b['check_out']))) ?></td>
                <td><?= $nights ?></td>
                <td><?= (int)$b['guests'] ?></td>
                <td>
                    <span class="badge <?= $statusBadge ?>">
                        <?= ucwords(str_replace('_', ' ', $b['status'])) ?>
                    </span>
                </td>
                <td>
                    <div class="action-btns">
                        <!-- View invoice -->
                        <a href="<?= base_url('billing/invoice/' . $b['booking_id']) ?>"
                           class="btn btn-outline btn-sm" title="Invoice">🧾</a>

                        <?php if ($is_staff): ?>
                            <?php if ($b['status'] === 'confirmed'): ?>
                            <form method="POST"
                                  action="<?= base_url('booking/checkin/' . $b['booking_id']) ?>">
                                <button class="btn btn-success btn-sm" title="Check In">↓ In</button>
                            </form>
                            <?php endif; ?>

                            <?php if ($b['status'] === 'checked_in'): ?>
                            <form method="POST"
                                  action="<?= base_url('booking/checkout/' . $b['booking_id']) ?>">
                                <button class="btn btn-primary btn-sm" title="Check Out">↑ Out</button>
                            </form>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (in_array($b['status'], ['confirmed'], true)): ?>
                        <form method="POST"
                              action="<?= base_url('booking/cancel/' . $b['booking_id']) ?>"
                              onsubmit="return confirm('Cancel booking #<?= $b['booking_id'] ?>?')">
                            <button class="btn btn-danger btn-sm" title="Cancel">✕</button>
                        </form>
                        <?php endif; ?>
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