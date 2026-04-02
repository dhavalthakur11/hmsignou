<?php
/** Admin feedback overview */

$feedbacks = $feedbacks ?? [];
$avg_rating = $avg_rating ?? 0;
?>

<div class="card mb-4">
    <div class="card-body">
        <div class="trend-grid">
            <div class="trend-item">
                <span class="trend-value star-value">
                    <?= number_format((float)$avg_rating, 1) ?> ★
                </span>
                <span class="trend-label">Average Rating</span>
            </div>
            <div class="trend-item">
                <span class="trend-value"><?= count($feedbacks) ?></span>
                <span class="trend-label">Total Reviews</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Customer Reviews</h3>
    </div>

    <div class="card-body p-0">
        <?php if (empty($feedbacks)): ?>
            <div class="empty-state">
                <div class="empty-icon">⭐</div>
                <h3>No feedback yet</h3>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Guest</th>
                            <th>Room</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbacks as $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f['guest_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($f['room_number'] ?? '—') ?></td>
                                <td>
                                    <span class="star-display">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="<?= $i <= (int)($f['rating'] ?? 0) ? 'star-on' : 'star-off' ?>">★</span>
                                        <?php endfor; ?>
                                    </span>
                                </td>
                                <td style="max-width:300px">
                                    <?= htmlspecialchars($f['feedback_text'] ?? '') ?>
                                </td>
                                <td>
                                    <?= !empty($f['created_at']) ? date('d M Y', strtotime($f['created_at'])) : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>