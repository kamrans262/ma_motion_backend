(() => {
    const shell = document.querySelector('[data-admin-shell]');
    const sidebar = document.querySelector('[data-sidebar]');
    const openButton = document.querySelector('[data-sidebar-open]');
    const closeButton = document.querySelector('[data-sidebar-close]');
    const overlay = document.querySelector('[data-sidebar-overlay]');

    if (!shell || !sidebar || !openButton || !overlay) {
        return;
    }

    const setOpen = (open) => {
        shell.classList.toggle('is-sidebar-open', open);
        openButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.classList.toggle('ma-no-scroll', open && window.innerWidth < 1024);

        if (open && window.innerWidth < 1024) {
            closeButton?.focus();
        }
    };

    openButton.addEventListener('click', () => setOpen(true));
    closeButton?.addEventListener('click', () => setOpen(false));
    overlay.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && shell.classList.contains('is-sidebar-open')) {
            setOpen(false);
            openButton.focus();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            setOpen(false);
        }
    });
})();
