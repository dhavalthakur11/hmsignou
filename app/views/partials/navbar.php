<?php
/**
 * Top Navigation Bar
 */
?>
<header class="topbar">
    <!-- Hamburger (mobile) -->
    <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
        <span></span><span></span><span></span>
    </button>

    <!-- Page title (set as $page_title in each view) -->
    <div class="topbar-title">
        <?= htmlspecialchars($page_title ?? 'Dashboard') ?>
    </div>

    <!-- Right actions -->
    <div class="topbar-actions">
        <!-- Notification bell -->
        <a href="<?= base_url('notification/index') ?>" class="topbar-btn" title="Notifications">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 01-3.46 0"/>
            </svg>
        </a>

        <!-- User dropdown -->
        <div class="user-dropdown" id="userDropdown">
            <button class="user-dropdown-toggle" onclick="toggleDropdown()">
                <span class="avatar-sm"><?= strtoupper(substr(user_name(), 0, 1)) ?></span>
                <span><?= htmlspecialchars(user_name()) ?></span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>
            <div class="dropdown-menu" id="dropdownMenu">
                <div class="dropdown-header">
                    <strong><?= htmlspecialchars(user_name()) ?></strong>
                    <small><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></small>
                </div>
                <hr class="dropdown-divider">
                <a href="<?= base_url('login/logout') ?>" class="dropdown-item logout">
                    Sign out
                </a>
            </div>
        </div>
    </div>
</header>
