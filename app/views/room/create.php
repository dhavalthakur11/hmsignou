<?php
/**
 * Add Room Form
 */
$old = $old ?? [];
?>
<div class="form-page-wrapper">
<div class="card form-card">
    <div class="card-header">
        <h3>Add New Room</h3>
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

        <form method="POST" action="<?= base_url('room/create') ?>" id="roomForm" novalidate>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="room_number">Room Number <span class="required">*</span></label>
                    <input type="text" id="room_number" name="room_number"
                           value="<?= htmlspecialchars($old['room_number'] ?? '') ?>"
                           placeholder="e.g. 101" required maxlength="10">
                </div>
                <div class="form-group">
                    <label for="room_type">Room Type <span class="required">*</span></label>
                    <select id="room_type" name="room_type" required>
                        <option value="">Select type…</option>
                        <?php foreach (['Standard','Deluxe','Suite','Presidential','Penthouse'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($old['room_type'] ?? '') === $t ? 'selected' : '' ?>>
                            <?= $t ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="floor">Floor <span class="required">*</span></label>
                    <input type="number" id="floor" name="floor" min="1" max="50"
                           value="<?= (int)($old['floor'] ?? 1) ?>" required>
                </div>
                <div class="form-group">
                    <label for="capacity">Max Guests <span class="required">*</span></label>
                    <input type="number" id="capacity" name="capacity" min="1" max="20"
                           value="<?= (int)($old['capacity'] ?? 2) ?>" required>
                </div>
                <div class="form-group">
                    <label for="price_per_night">Price per Night (₹) <span class="required">*</span></label>
                    <input type="number" id="price_per_night" name="price_per_night"
                           min="1" step="0.01"
                           value="<?= htmlspecialchars($old['price_per_night'] ?? '') ?>"
                           placeholder="e.g. 2500" required>
                </div>
                <div class="form-group">
                    <label for="status">Initial Status</label>
                    <select id="status" name="status">
                        <?php foreach (['available','maintenance'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($old['status'] ?? 'available') === $s ? 'selected' : '' ?>>
                            <?= ucfirst($s) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="amenities">Amenities <small>(comma separated)</small></label>
                <input type="text" id="amenities" name="amenities"
                       value="<?= htmlspecialchars($old['amenities'] ?? '') ?>"
                       placeholder="WiFi, AC, TV, Mini Bar">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"
                          placeholder="Brief room description…"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Room</button>
                <a href="<?= base_url('room/index') ?>" class="btn btn-outline">Cancel</a>
            </div>

        </form>
    </div>
</div>
</div>