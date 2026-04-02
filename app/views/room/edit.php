<?php
/**
 * Edit Room Form — pre-filled from $room data
 */
?>
<div class="form-page-wrapper">
<div class="card form-card">
    <div class="card-header">
        <h3>Edit Room — <span class="text-muted"><?= htmlspecialchars($room['room_number']) ?></span></h3>
        <a href="<?= base_url('room/index') ?>" class="btn btn-outline btn-sm">← Back</a>
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

        <form method="POST"
              action="<?= base_url('room/edit/' . $room['room_id']) ?>"
              id="roomForm" novalidate>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="room_number">Room Number <span class="required">*</span></label>
                    <input type="text" id="room_number" name="room_number"
                           value="<?= htmlspecialchars($room['room_number']) ?>" required maxlength="10">
                </div>
                <div class="form-group">
                    <label for="room_type">Room Type <span class="required">*</span></label>
                    <select id="room_type" name="room_type" required>
                        <?php foreach (['Standard','Deluxe','Suite','Presidential','Penthouse'] as $t): ?>
                        <option value="<?= $t ?>" <?= $room['room_type'] === $t ? 'selected' : '' ?>>
                            <?= $t ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="floor">Floor <span class="required">*</span></label>
                    <input type="number" id="floor" name="floor" min="1" max="50"
                           value="<?= (int)$room['floor'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="capacity">Max Guests <span class="required">*</span></label>
                    <input type="number" id="capacity" name="capacity" min="1" max="20"
                           value="<?= (int)$room['capacity'] ?>" required>
                </div>
                <div class="form-group">
                    <label for="price_per_night">Price per Night (₹) <span class="required">*</span></label>
                    <input type="number" id="price_per_night" name="price_per_night"
                           min="1" step="0.01"
                           value="<?= htmlspecialchars($room['price_per_night']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <?php foreach (['available','booked','maintenance','checkout'] as $s): ?>
                        <option value="<?= $s ?>" <?= $room['status'] === $s ? 'selected' : '' ?>>
                            <?= ucfirst($s) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="amenities">Amenities</label>
                <input type="text" id="amenities" name="amenities"
                       value="<?= htmlspecialchars($room['amenities'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"><?= htmlspecialchars($room['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Room</button>
                <a href="<?= base_url('room/index') ?>" class="btn btn-outline">Cancel</a>
            </div>

        </form>
    </div>
</div>
</div>