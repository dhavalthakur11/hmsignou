<?php
/**
 * Flash Alert Partial
 * Displays one-time session flash messages.
 */
$flash = get_flash();
if ($flash):
?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible" role="alert" id="flashAlert">
    <span><?= htmlspecialchars($flash['message']) ?></span>
    <button onclick="this.parentElement.remove()" class="alert-close" aria-label="Dismiss">&times;</button>
</div>
<?php endif; ?>