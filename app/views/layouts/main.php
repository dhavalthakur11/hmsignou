<?php
/**
 * Main Layout
 * Every authenticated view renders inside this shell.
 * $content_view is set by Controller::view() before this file is loaded.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Hotel Management System' ?></title>
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
</head>
<body class="app-body">

<!-- ── Sidebar ── -->
<?php require_once APP_PATH . '/views/layouts/sidebar.php'; ?>

<!-- ── Main content area ── -->
<div class="main-wrapper" id="mainWrapper">

    <!-- Top navbar -->
    <?php require_once APP_PATH . '/views/partials/navbar.php'; ?>

    <!-- Page content -->
    <main class="content-area">

        <!-- Flash alerts -->
        <?php require_once APP_PATH . '/views/partials/alerts.php'; ?>

        <!-- Actual view injected here -->
        <?php require_once $content_view; ?>

    </main>

    <!-- Footer -->
    <?php require_once APP_PATH . '/views/layouts/footer.php'; ?>

</div>

<script src="<?= asset_url('js/auth/app.js') ?>"></script>
</body>
</html>