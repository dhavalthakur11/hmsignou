<?php $old = $old ?? []; ?>
<div class="form-page-wrapper">
<div class="card form-card">
    <div class="card-header">
        <h3>Add Employee</h3>
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

        <form method="POST" action="<?= base_url('employee/create') ?>" novalidate>

            <p class="section-label">Account Details</p>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="name"
                           value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email"
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone"
                           value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="password"
                           placeholder="Min 8 characters" required minlength="8">
                </div>
                <div class="form-group">
                    <label>System Role <span class="required">*</span></label>
                    <select name="role">
                        <option value="receptionist"
                            <?= ($old['role'] ?? '') === 'receptionist' ? 'selected' : '' ?>>
                            Receptionist
                        </option>
                        <option value="admin"
                            <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
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
                           value="<?= htmlspecialchars($old['department'] ?? '') ?>"
                           placeholder="e.g. Front Desk, Housekeeping" required>
                </div>
                <div class="form-group">
                    <label>Designation <span class="required">*</span></label>
                    <input type="text" name="designation"
                           value="<?= htmlspecialchars($old['designation'] ?? '') ?>"
                           placeholder="e.g. Senior Receptionist" required>
                </div>
                <div class="form-group">
                    <label>Monthly Salary (₹) <span class="required">*</span></label>
                    <input type="number" name="salary" min="1" step="0.01"
                           value="<?= htmlspecialchars($old['salary'] ?? '') ?>"
                           placeholder="e.g. 35000" required>
                </div>
                <div class="form-group">
                    <label>Hire Date</label>
                    <input type="date" name="hire_date"
                           value="<?= htmlspecialchars($old['hire_date'] ?? date('Y-m-d')) ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Employee</button>
                <a href="<?= base_url('employee/index') ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>