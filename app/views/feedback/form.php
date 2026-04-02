<?php
$old = $old ?? [];
$errors = $errors ?? [];
$my_stays = $my_stays ?? [];
?>

<div class="form-page-wrapper">
    <div class="card form-card">
        <div class="card-header">
            <h3>Leave Feedback</h3>
            <a href="<?= base_url('customer/dashboard') ?>" class="btn btn-outline btn-sm">← Back</a>
        </div>

        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul class="error-list">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (empty($my_stays)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🏨</div>
                    <h3>No completed stays yet</h3>
                    <p>You can leave feedback after checking out from a stay.</p>
                    <a href="<?= base_url('booking/create') ?>" class="btn btn-primary">Book a Room</a>
                </div>
            <?php else: ?>

                <form method="POST" action="<?= base_url('feedback/form') ?>" novalidate>

                    <div class="form-group">
                        <label>Select Your Stay <span class="required">*</span></label>
                        <select name="booking_id" required>
                            <option value="">Choose a booking…</option>
                            <?php foreach ($my_stays as $s): ?>
                                <option value="<?= (int)($s['booking_id'] ?? 0) ?>"
                                    <?= (($old['booking_id'] ?? '') == ($s['booking_id'] ?? '')) ? 'selected' : '' ?>>
                                    Room <?= htmlspecialchars($s['room_number'] ?? '') ?>
                                    — <?= !empty($s['check_in']) ? date('d M Y', strtotime($s['check_in'])) : '-' ?>
                                    to <?= !empty($s['check_out']) ? date('d M Y', strtotime($s['check_out'])) : '-' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Rating <span class="required">*</span></label>
                        <div class="star-rating" id="starRating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button"
                                        class="star-btn <?= ((int)($old['rating'] ?? 0) >= $i) ? 'active' : '' ?>"
                                        data-value="<?= $i ?>"
                                        onclick="setRating(<?= $i ?>)">★</button>
                            <?php endfor; ?>
                        </div>

                        <input type="hidden" name="rating" id="ratingInput"
                               value="<?= (int)($old['rating'] ?? 0) ?>">

                        <small class="form-hint" id="ratingHint">Click a star to rate.</small>
                    </div>

                    <div class="form-group">
                        <label for="comment">Your Review <span class="required">*</span></label>
                        <textarea id="comment"
                                  name="comment"
                                  rows="4"
                                  placeholder="Tell us about your experience…"
                                  required
                                  minlength="5"><?= htmlspecialchars($old['comment'] ?? '') ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Submit Feedback</button>
                        <a href="<?= base_url('customer/dashboard') ?>" class="btn btn-outline">Cancel</a>
                    </div>
                </form>

                <script>
                    const labels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];

                    function setRating(val) {
                        document.getElementById('ratingInput').value = val;
                        document.getElementById('ratingHint').textContent = labels[val];

                        document.querySelectorAll('.star-btn').forEach((btn, i) => {
                            btn.classList.toggle('active', i < val);
                        });
                    }

                    const initVal = parseInt(document.getElementById('ratingInput').value || '0', 10);
                    if (initVal > 0) {
                        document.getElementById('ratingHint').textContent = labels[initVal];
                    }
                </script>

            <?php endif; ?>
        </div>
    </div>
</div>