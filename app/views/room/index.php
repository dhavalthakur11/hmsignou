<?php
/**
 * Room Index — filterable grid of all rooms with status badges
 */
?>

<!-- ── Mini stats row -->
<div class="stats-grid stats-grid-4 mb-4">
    <?php
    $statDefs = [
        ['Total Rooms',    $stats['total'],       'stat-blue'],
        ['Available',      $stats['available'],   'stat-green'],
        ['Booked',         $stats['booked'],      'stat-orange'],
        ['Maintenance',    $stats['maintenance'], 'stat-purple'],
    ];
    foreach ($statDefs as [$label, $val, $cls]): ?>
    <div class="stat-card <?= $cls ?>">
        <div class="stat-content">
            <span class="stat-label"><?= $label ?></span>
            <span class="stat-value"><?= (int)$val ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Filter bar -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= base_url('room/index') ?>" class="filter-form">
            <div class="filter-row">
                <div class="form-group mb-0">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Statuses</option>
                        <?php foreach (['available','booked','maintenance','checkout'] as $s): ?>
                        <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>>
                            <?= ucfirst($s) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label>Room Type</label>
                    <select name="room_type">
                        <option value="">All Types</option>
                        <?php foreach ($room_types as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>"
                            <?= $filters['room_type'] === $type ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label>Floor</label>
                    <select name="floor">
                        <option value="">All Floors</option>
                        <?php for ($f = 1; $f <= 10; $f++): ?>
                        <option value="<?= $f ?>" <?= (string)$filters['floor'] === (string)$f ? 'selected' : '' ?>>
                            Floor <?= $f ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="<?= base_url('room/index') ?>" class="btn btn-outline btn-sm">Reset</a>
                    <?php if (user_role() === 'admin'): ?>
                    <a href="<?= base_url('room/create') ?>" class="btn btn-success btn-sm">+ Add Room</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ── Room cards grid -->
<?php if (empty($rooms)): ?>
    <div class="empty-state">
        <div class="empty-icon">🏨</div>
        <h3>No rooms found</h3>
        <p>Try adjusting your filters or add a new room.</p>
        <?php if (user_role() === 'admin'): ?>
        <a href="<?= base_url('room/create') ?>" class="btn btn-primary">Add First Room</a>
        <?php endif; ?>
    </div>
<?php else: ?>
<div class="room-grid">
    <?php foreach ($rooms as $room): ?>
    <?php
        $statusClass = match($room['status']) {
            'available'   => 'room-available',
            'booked'      => 'room-booked',
            'maintenance' => 'room-maintenance',
            default       => 'room-checkout',
        };
        $badgeClass = match($room['status']) {
            'available'   => 'badge-green',
            'booked'      => 'badge-blue',
            'maintenance' => 'badge-orange',
            default       => 'badge-gray',
        };
    ?>
    <div class="room-card <?= $statusClass ?>">
        <!-- Status strip -->
        <div class="room-card-header">
            <div class="room-number-badge">Room <?= htmlspecialchars($room['room_number']) ?></div>
            <span class="badge <?= $badgeClass ?>">
                <?= ucfirst($room['status']) ?>
            </span>
        </div>

        <!-- Room info -->
        <div class="room-card-body">
            <h3 class="room-type"><?= htmlspecialchars($room['room_type']) ?></h3>
            <div class="room-meta">
                <span title="Floor">🏢 Floor <?= (int)$room['floor'] ?></span>
                <span title="Capacity">👥 <?= (int)$room['capacity'] ?> guests</span>
            </div>
            <div class="room-price">
                ₹<?= number_format((float)$room['price_per_night'], 0) ?>
                <span class="price-unit">/ night</span>
            </div>
            <?php if (!empty($room['amenities'])): ?>
            <div class="room-amenities">
                <?php foreach (explode(',', $room['amenities']) as $a): ?>
                <span class="amenity-tag"><?= htmlspecialchars(trim($a)) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="room-card-footer">
            <?php if (user_role() === 'admin'): ?>
            <a href="<?= base_url('room/edit/' . $room['room_id']) ?>" class="btn btn-outline btn-sm">Edit</a>

            <!-- Quick status change -->
            <div class="dropdown-inline" id="statusDrop<?= $room['room_id'] ?>">
                <button class="btn btn-outline btn-sm"
                        onclick="toggleStatusDrop(<?= $room['room_id'] ?>)">
                    Status ▾
                </button>
                <div class="status-dropdown" id="statusMenu<?= $room['room_id'] ?>">
                    <?php foreach (['available','booked','maintenance','checkout'] as $s):
                        if ($s === $room['status']) continue; ?>
                    <form method="POST" action="<?= base_url('room/status/' . $room['room_id']) ?>">
                        <input type="hidden" name="status" value="<?= $s ?>">
                        <button type="submit" class="status-option">
                            → <?= ucfirst($s) ?>
                        </button>
                    </form>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Delete (only if no bookings) -->
            <form method="POST" action="<?= base_url('room/delete/' . $room['room_id']) ?>"
                  onsubmit="return confirm('Delete Room <?= htmlspecialchars($room['room_number']) ?>? This cannot be undone.')">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>

            <?php else: ?>
            <?php if ($room['status'] === 'available'): ?>
            <a href="<?= base_url('booking/create?room_id=' . $room['room_id']) ?>"
               class="btn btn-primary btn-sm">Book Now</a>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function toggleStatusDrop(id) {
    const menu = document.getElementById('statusMenu' + id);
    // Close all others first
    document.querySelectorAll('.status-dropdown').forEach(m => {
        if (m !== menu) m.classList.remove('open');
    });
    menu.classList.toggle('open');
}
// Close dropdowns when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('.dropdown-inline')) {
        document.querySelectorAll('.status-dropdown').forEach(m => m.classList.remove('open'));
    }
});
</script>