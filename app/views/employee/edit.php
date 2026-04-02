<?php /** Edit employee — same layout as create, pre-filled */ ?>
<div class="form-page-wrapper">
<div class="card form-card">
    <div class="card-header">
        <h3>Edit Employee — <?= htmlspecialchars($emp['name']) ?></h3>
        <a href="<?= base_url('employee/index') ?>" class="btn btn-outline btn-sm">← Back</a>
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
              action="<?= base_url('employee/edit/' . $emp['employee_id']) ?>" novalidate>

            <p class="section-label">Account Details</p>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="name"
                           value="<?= htmlspecialchars($emp['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?= htmlspecialchars($emp['email']) ?>" disabled>
                    <small class="form-hint">Email cannot be changed.</small>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone"
                           value="<?= htmlspecialchars($emp['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>System Role</label>
                    <select name="role">
                        <option value="receptionist"
                            <?= $emp['role'] === 'receptionist' ? 'selected' : '' ?>>
                            Receptionist
                        </option>
                        <option value="admin"
                            <?= $emp['role'] === 'admin' ? 'selected' : '' ?>>
                            Admin
                        </option>
                    </select>
                </div>
            </div>

            <p class="section-label mt-4">Employment Details</p>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Department <span class="required">*</span></label>
                    <input type="text" name="department"
                           value="<?= htmlspecialchars($emp['department'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Designation <span class="required">*</span></label>
                    <input type="text" name="designation"
                           value="<?= htmlspecialchars($emp['designation'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Monthly Salary (₹) <span class="required">*</span></label>
                    <input type="number" name="salary" min="1" step="0.01"
                           value="<?= htmlspecialchars($emp['salary'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Hire Date</label>
                    <input type="date" name="hire_date"
                           value="<?= htmlspecialchars(
                               $emp['hire_date']
                                   ? date('Y-m-d', strtotime($emp['hire_date']))
                                   : date('Y-m-d')
                           ) ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active">
                        <option value="1" <?= $emp['is_active'] ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= !$emp['is_active'] ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Employee</button>
                <a href="<?= base_url('employee/index') ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>