<?php
/**
 * Sidebar Navigation
 * Menu items are filtered by the current user's role.
 */
$role        = user_role();
$currentUrl  = $_SERVER['REQUEST_URI'] ?? '';

/**
 * Nav items: [label, url_path, icon_svg, allowed_roles[]]
 */
$navItems = [
    // Admin only
    ['Dashboard',    'admin/dashboard',         'grid',       ['admin']],
    ['Rooms',        'room/index',               'home',       ['admin', 'receptionist']],
    ['Bookings',     'booking/index',            'calendar',   ['admin', 'receptionist']],
    ['Customers',    'customer/list',             'users',      ['admin', 'receptionist']],
    ['Employees',    'employee/index',           'briefcase',  ['admin']],
    ['Billing',      'billing/index',            'credit-card',['admin', 'receptionist']],
    ['Reports',      'report/index',             'bar-chart',  ['admin']],
    ['Feedback',     'feedback/index',           'message-square', ['admin']],
    ['Logs',         'logs/index',               'activity',   ['admin']],
    ['Notifications','notification/index',       'bell',       ['admin', 'receptionist']],
    // Receptionist dashboard
    ['Dashboard',    'receptionist/dashboard',   'grid',       ['receptionist']],
    // Customer
    ['My Dashboard', 'customer/dashboard',       'grid',       ['customer']],
    ['Book a Room',  'booking/create',           'plus-circle',['customer']],
    ['My Bookings',  'booking/mybookings',       'list',       ['customer']],
    ['Feedback',     'feedback/form',            'star',       ['customer']],
];

function sidebarIcon(string $name): string {
    $icons = [
        'grid'           => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>',
        'home'           => '<path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/><polyline points="9 21 9 12 15 12 15 21"/>',
        'calendar'       => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'users'          => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>',
        'briefcase'      => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="12"/>',
        'credit-card'    => '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
        'bar-chart'      => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'message-square' => '<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>',
        'activity'       => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
        'bell'           => '<path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>',
        'plus-circle'    => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>',
        'list'           => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
        'star'           => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
    ];
    $paths = $icons[$name] ?? '<circle cx="12" cy="12" r="10"/>';
    return '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>';
}
?>

<aside class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
        <span class="brand-icon-sm">🏨</span>
        <span class="brand-name">GrandHotel</span>
        <button class="sidebar-close" id="sidebarClose" aria-label="Close sidebar">&#10005;</button>
    </div>

    <!-- User info -->
    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr(user_name(), 0, 1)) ?></div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars(user_name()) ?></span>
            <span class="user-role-badge"><?= ucfirst($role) ?></span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav" aria-label="Main navigation">
        <ul>
            <?php foreach ($navItems as [$label, $path, $icon, $roles]): ?>
                <?php if (!in_array($role, $roles, true)) continue; ?>
                <?php
                    $href    = base_url($path);
                    $active  = str_contains($currentUrl, explode('/', $path)[0]);
                    $classes = 'nav-item' . ($active ? ' active' : '');
                ?>
                <li>
                    <a href="<?= $href ?>" class="<?= $classes ?>">
                        <?= sidebarIcon($icon) ?>
                        <span><?= htmlspecialchars($label) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Logout -->
    <div class="sidebar-footer">
        <a href="<?= base_url('login/logout') ?>" class="nav-item logout-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Logout</span>
        </a>
    </div>
</aside>

<!-- Mobile overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>