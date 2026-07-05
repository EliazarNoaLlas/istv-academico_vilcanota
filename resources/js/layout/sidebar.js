/**
 * Interaccion visual del sidebar. localStorage solo guarda la preferencia
 * de "colapsado si/no" en pantallas pequenas - nunca datos academicos.
 */
const STORAGE_KEY = 'istv_sidebar_collapsed';

export function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
    const overlay = document.querySelector('.sidebar-overlay');

    if (!sidebar) return;

    const setOpen = (open) => {
        sidebar.classList.toggle('open', open);
        overlay?.classList.toggle('show', open);
        localStorage.setItem(STORAGE_KEY, open ? '0' : '1');
    };

    toggleButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            setOpen(!sidebar.classList.contains('open'));
        });
    });

    overlay?.addEventListener('click', () => setOpen(false));
}
