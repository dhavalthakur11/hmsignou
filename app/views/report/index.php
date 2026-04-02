<?php
/**
 * Reports Dashboard — revenue, occupancy, trends.
 */
$occ = $occupancy;
$rev = $revenue;
$trn = $trends;
$occPct = $occ['total'] > 0
    ? round(($occ['booked'] / $occ['total']) * 100) : 0;
?>

<!-- ── Revenue cards ─────────────────────────────────────────── -->
<div class="stats-grid mb-4">
    <div class="stat-card stat-green">
        <div class="stat-content">
            <span class="stat-label">Revenue Today</span>
            <span class="stat-value">₹<?= number_format((float)$rev['today'], 0) ?></span>
        </div>
    </div>
    <div class="stat-card stat-blue">
        <div class="stat-content">
            <span class="stat-label">This Month</span>
            <span class="stat-value">₹<?= number_format((float)$rev['this_month'], 0) ?></span>
        </div>
    </div>
    <div class="stat-card stat-purple">
        <div class="stat-content">
            <span class="stat-label">All Time</span>
            <span class="stat-value">₹<?= number_format((float)$rev['all_time'], 0) ?></span>
        </div>
    </div>
    <div class="stat-card stat-orange">
        <div class="stat-content">
            <span class="stat-label">Occupancy Rate</span>
            <span class="stat-value"><?= $occPct ?>%</span>
            <span class="stat-sub"><?= (int)$occ['booked'] ?>/<?= (int)$occ['total'] ?> rooms</span>
        </div>
    </div>
</div>

<!-- ── Booking trends ────────────────────────────────────────── -->
<div class="two-col-grid mb-4">
    <div class="card">
        <div class="card-header"><h3>Booking Trends</h3></div>
        <div class="card-body">
            <div class="trend-grid">
                <div class="trend-item">
                    <span class="trend-value"><?= (int)$trn['today'] ?></span>
                    <span class="trend-label">Today</span>
                </div>
                <div class="trend-item">
                    <span class="trend-value"><?= (int)$trn['this_week'] ?></span>
                    <span class="trend-label">This Week</span>
                </div>
                <div class="trend-item">
                    <span class="trend-value"><?= (int)$trn['this_month'] ?></span>
                    <span class="trend-label">This Month</span>
                </div>
            </div>
            <hr style="margin:16px 0;border-color:var(--border-color)">
            <h4 style="font-size:.85rem;margin-bottom:12px">Bookings by Status</h4>
            <?php foreach ($by_status as $s): ?>
            <?php
                $pct = 0;
                $total_bookings = array_sum(array_column($by_status, 'cnt'));
                $pct = $total_bookings > 0 ? round(($s['cnt'] / $total_bookings) * 100) : 0;
                $barClass = match($s['status']) {
                    'confirmed'   => 'bar-blue',
                    'checked_in'  => 'bar-green',
                    'checked_out' => 'bar-gray',
                    'cancelled'   => 'bar-red',
                    default       => 'bar-gray',
                };
            ?>
            <div class="bar-row">
                <span class="bar-label"><?= ucwords(str_replace('_',' ',$s['status'])) ?></span>
                <div class="bar-track">
                    <div class="bar-fill <?= $barClass ?>" style="width:<?= $pct ?>%"></div>
                </div>
                <span class="bar-count"><?= (int)$s['cnt'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Top rooms -->
    <div class="card">
        <div class="card-header"><h3>Top Rooms by Bookings</h3></div>
        <div class="card-body p-0">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Type</th>
                        <th>Bookings</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($top_rooms)): ?>
                    <tr><td colspan="4" class="empty-row">No data yet.</td></tr>
                <?php else: ?>
                <?php foreach ($top_rooms as $i => $r): ?>
                <tr>
                    <td>
                        <span class="rank-badge"><?= $i + 1 ?></span>
                        Room <?= htmlspecialchars($r['room_number']) ?>
                    </td>
                    <td><?= htmlspecialchars($r['room_type']) ?></td>
                    <td><?= (int)$r['bookings'] ?></td>
                    <td>₹<?= number_format((float)$r['revenue'], 0) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── Monthly revenue chart (CSS bars) ──────────────────────── -->
<div class="card mb-4">
    <div class="card-header"><h3>Monthly Revenue (last 12 months)</h3></div>
    <div class="card-body">
        <?php if (empty($monthly)): ?>
            <div class="empty-state" style="padding:28px">
                <p>No paid invoices recorded yet.</p>
            </div>
        <?php else:
            $maxRev = max(array_column($monthly, 'revenue')) ?: 1;
        ?>
        <div class="chart-bar-group">
            <?php foreach ($monthly as $m): ?>
            <?php $ht = round(($m['revenue'] / $maxRev) * 180); ?>
            <div class="chart-bar-col">
                <span class="chart-bar-value">
                    ₹<?= number_format((float)$m['revenue'] / 1000, 1) ?>k
                </span>
                <div class="chart-bar-wrap">
                    <div class="chart-bar-fill" style="height:<?= $ht ?>px"></div>
                </div>
                <span class="chart-bar-label"><?= htmlspecialchars($m['month']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Occupancy breakdown ───────────────────────────────────── -->
<div class="card">
    <div class="card-header"><h3>Room Occupancy Breakdown</h3></div>
    <div class="card-body">
        <div class="occupancy-bar" style="height:14px">
            <div class="occupancy-fill" style="width:<?= $occPct ?>%"></div>
        </div>
        <div class="occ-detail-grid">
            <div class="occ-detail-item">
                <span class="legend-dot dot-booked"></span>
                <span>Booked</span>
                <strong><?= (int)$occ['booked'] ?></strong>
            </div>
            <div class="occ-detail-item">
                <span class="legend-dot dot-available"></span>
                <span>Available</span>
                <strong><?= (int)$occ['available'] ?></strong>
            </div>
            <div class="occ-detail-item">
                <span class="legend-dot dot-maintenance"></span>
                <span>Maintenance</span>
                <strong><?= (int)$occ['maintenance'] ?></strong>
            </div>
            <div class="occ-detail-item">
                <span class="legend-dot" style="background:var(--text-muted)"></span>
                <span>Total Rooms</span>
                <strong><?= (int)$occ['total'] ?></strong>
            </div>
        </div>
    </div>
</div>