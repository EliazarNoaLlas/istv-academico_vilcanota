/**
 * Menus desplegables del header (perfil, notificaciones). Solo maneja
 * abrir/cerrar visual; el contenido real se conecta cuando el modulo
 * de notificaciones tenga su vista propia (Fase 7).
 */
export function initDropdowns() {
    document.querySelectorAll('[data-dropdown-toggle]').forEach((trigger) => {
        const target = document.querySelector(trigger.dataset.dropdownToggle);
        if (!target) return;

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            target.classList.toggle('show');
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu.show').forEach((el) => el.classList.remove('show'));
    });
}
