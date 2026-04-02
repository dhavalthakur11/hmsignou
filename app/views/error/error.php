<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error — Hotel Management</title>
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
</head>
<body class="auth-page">
<div style="display:flex;flex-direction:column;align-items:center;
            justify-content:center;min-height:100vh;text-align:center;padding:24px">
    <div style="font-size:4rem;margin-bottom:16px">🚫</div>
    <h1 style="font-size:1.5rem;margin-bottom:8px">Page Not Found</h1>
    <p style="color:var(--text-secondary);margin-bottom:24px">
        <?= htmlspecialchars($message ?? 'The page you are looking for does not exist.') ?>
    </p>
    <a href="<?= base_url('login/index') ?>" class="btn btn-primary">Go to Login</a>
</div>
</body>
</html>