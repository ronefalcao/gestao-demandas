/**
 * Placeholder for project-wide JavaScript.
 * Tailwind/Vite will bundle this file.
 */

document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (!toggleButton || !sidebar || !overlay) {
        return;
    }

    const closeMenu = () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    };

    const openMenu = () => {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    };

    const toggleMenu = () => {
        if (sidebar.classList.contains('-translate-x-full')) {
            openMenu();
        } else {
            closeMenu();
        }
    };

    toggleButton.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', closeMenu);

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) {
                closeMenu();
            }
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            openMenu();
        } else {
            closeMenu();
        }
    });

    if (window.innerWidth >= 1024) {
        openMenu();
    } else {
        closeMenu();
    }

    const alerts = document.querySelectorAll('[data-alert]');
    alerts.forEach((alert) => {
        const closeButton = alert.querySelector('[data-alert-close]');
        const removeAlert = () => alert.remove();

        closeButton?.addEventListener('click', removeAlert);

        const timeout = parseInt(alert.dataset.autoDismiss ?? '0', 10);
        if (timeout > 0) {
            setTimeout(removeAlert, timeout);
        }
    });
});


