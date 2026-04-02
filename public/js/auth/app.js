// ── Sidebar toggle (mobile)
const sidebar        = document.getElementById('sidebar');
const menuToggle     = document.getElementById('menuToggle');
const sidebarClose   = document.getElementById('sidebarClose');
const sidebarOverlay = document.getElementById('sidebarOverlay');

function openSidebar() {
    sidebar?.classList.add('open');
    sidebarOverlay?.classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    sidebar?.classList.remove('open');
    sidebarOverlay?.classList.remove('open');
    document.body.style.overflow = '';
}

menuToggle?.addEventListener('click', openSidebar);
sidebarClose?.addEventListener('click', closeSidebar);
sidebarOverlay?.addEventListener('click', closeSidebar);

// ── User dropdown 
function toggleDropdown() {
    document.getElementById('dropdownMenu')?.classList.toggle('open');
}

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('dropdownMenu')?.classList.remove('open');
    }
});

// ── Auto-dismiss flash alerts 
const flashAlert = document.getElementById('flashAlert');
if (flashAlert) {
    setTimeout(() => {
        flashAlert.style.transition = 'opacity 0.4s';
        flashAlert.style.opacity    = '0';
        setTimeout(() => flashAlert.remove(), 400);
    }, 4000);
}