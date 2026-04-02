<?php /** Employee listing */ ?>

<div class="stats-grid stats-grid-3 mb-4">
    <div class="stat-card stat-blue">
        <div class="stat-content">
            <span class="stat-label">Total Employees</span>
            <span class="stat-value"><?= (int)$active_count ?></span>
            <span class="stat-sub">active staff</span>
        </div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-content">
            <span class="stat-label">Departments</span>
            <span class="stat-value"><?= count($departments) ?></span>
        </div>
    </div>
    <div class="stat-card stat-orange">
        <div class="stat-content">
            <span class="stat-label">Monthly Salary</span>
            <span class="stat-value">₹<?= number_format($total_salary, 0) ?></span>
        </div>
    </div>
</div>

<!-- Filter + Add bar -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= base_url('employee/index') ?>" class="filter-form">
            <div class="filter-row">
                <div class="form-group mb-0">
                    <label>Department</label>
                    <select name="department">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?= htmlspecialchars($dept) ?>"
                            <?= ($filters['department'] ?? '') === $dept ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label>Status</label>
                    <select name="is_active">
                        <option value="">All</option>
                        <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="<?= base_url('employee/index') ?>" class="btn btn-outline btn-sm">Reset</a>
                    <a href="<?= base_url('employee/create') ?>" class="btn btn-success btn-sm">+ Add Employee</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Staff Directory</h3>
        <span class="badge badge-gray"><?= count($employees) ?> records</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($employees)): ?>
            <div class="empty-state"><div class="empty-icon">👨‍💼</div>
                <h3>No employees found</h3></div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Role</th>
                    <th>Salary</th>
                    <th>Hire Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($employees as $e): ?>
            <tr>
                <td>
                    <div class="guest-cell">
                        <span class="guest-avatar"><?= strtoupper(substr($e['name'], 0, 1)) ?></span>
                        <div>
                            <div><?= htmlspecialchars($e['name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($e['email']) ?></small>
                        </div>
                    </div>
                </td>
                <td><?= htmlspecialchars($e['department'] ?? '—') ?></td>
                <td><?= htmlspecialchars($e['designation'] ?? '—') ?></td>
                <td><span class="badge badge-blue"><?= ucfirst($e['role']) ?></span></td>
                <td>₹<?= number_format((float)$e['salary'], 0) ?></td>
                <td><?= $e['hire_date'] ? date('d M Y', strtotime($e['hire_date'])) : '—' ?></td>
                <td>
                    <span class="badge <?= $e['is_active'] ? 'badge-green' : 'badge-red' ?>">
                        <?= $e['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="<?= base_url('employee/edit/' . $e['employee_id']) ?>"
                           class="btn btn-outline btn-sm">Edit</a>
                        <?php if ($e['is_active']): ?>
                        <form method="POST"
                              action="<?= base_url('employee/deactivate/' . $e['employee_id']) ?>"
                              onsubmit="return confirm('Deactivate this employee?')">
                            <button class="btn btn-danger btn-sm">Deactivate</button>
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