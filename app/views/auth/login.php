<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management — <?= isset($register_mode) ? 'Register' : 'Login' ?></title>
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
</head>
<body class="auth-page">

<div class="auth-wrapper">

    <!-- Brand panel -->
    <div class="auth-brand">
        <div class="brand-content">
            <div class="brand-icon">🏨</div>
            <h1>Grand Hotel</h1>
            <p>Management System</p>
            <ul class="brand-features">
                <li>Room & Booking Management</li>
                <li>Real-time Availability</li>
                <li>Billing & Invoicing</li>
                <li>Staff & Reports</li>
            </ul>
        </div>
    </div>

    <!-- Form panel -->
    <div class="auth-form-panel">
        <div class="auth-form-container">

            <!-- Tab toggle -->
            <div class="auth-tabs">
                <a href="<?= base_url('login/index') ?>"
                   class="auth-tab <?= !isset($register_mode) ? 'active' : '' ?>">Sign In</a>
                <a href="<?= base_url('login/register') ?>"
                   class="auth-tab <?= isset($register_mode) ? 'active' : '' ?>">Register</a>
            </div>

            <!-- Flash / error messages -->
            <?php $flash = get_flash(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul class="error-list">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- ── LOGIN FORM ── -->
            <?php if (!isset($register_mode)): ?>
            <form action="<?= base_url('login/authenticate') ?>" method="POST" id="loginForm" novalidate>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com"
                           autocomplete="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon-wrap">
                        <input type="password" id="password" name="password"
                               placeholder="Enter your password" autocomplete="current-password" required>
                        <button type="button" class="toggle-pw" onclick="togglePassword('password')">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Sign In</button>
                <p class="auth-hint">
                    Admin: <strong>admin@hotel.com</strong> / <strong>password</strong>
                    &nbsp;&middot;&nbsp;
                    Customer: <strong>john@example.com</strong> / <strong>password</strong>
                </p>
            </form>

            <!-- ── REGISTER FORM ── -->
            <?php else: ?>
            <form action="<?= base_url('login/register') ?>" method="POST" id="registerForm" novalidate>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Jane Smith"
                           autocomplete="name" required minlength="2">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com"
                           autocomplete="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="+91 98765 43210">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon-wrap">
                        <input type="password" id="password" name="password"
                               placeholder="Minimum 8 characters" autocomplete="new-password" required minlength="8">
                        <button type="button" class="toggle-pw" onclick="togglePassword('password')">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <div class="pw-strength" id="pwStrength">
                        <div class="pw-bar" id="pwBar"></div>
                    </div>
                    <small id="pwHint" class="form-hint"></small>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Create Account</button>
            </form>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="<?= asset_url('js/auth.js') ?>"></script>
</body>
</html>