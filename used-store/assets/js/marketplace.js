/**
 * Marketplace filter sidebar toggle
 */
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('filterToggle');
    const sidebar = document.getElementById('filterSidebar');
    const close = document.getElementById('filterClose');

    toggle?.addEventListener('click', () => sidebar?.classList.add('active'));
    close?.addEventListener('click', () => sidebar?.classList.remove('active'));

    document.addEventListener('click', (e) => {
        if (sidebar?.classList.contains('active') &&
            !sidebar.contains(e.target) && !toggle?.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });

    const searchInput = document.getElementById('search');
    let debounceTimer;
    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            document.getElementById('filterForm')?.submit();
        }, 600);
    });
});
