/**
 * Sidebar Module - Modern, Interactive, Collapsible Sidebar
 * Handles: collapse/expand, keyboard navigation, mobile offcanvas, persistence
 */

export class Sidebar {
    constructor(options = {}) {
        this.sidebar = document.querySelector('.app-sidebar');
        this.collapseBtn = document.getElementById('sidebarCollapseBtn');
        this.mainContent = document.querySelector('.app-main');
        this.topbar = document.querySelector('.app-topbar');
        this.toggleBtn = document.querySelector('.sidebar-toggle');

        this.options = {
            collapsedClass: 'collapsed',
            openClass: 'is-open',
            mainCollapsedClass: 'app-main--sidebar-collapsed',
            topbarCollapsedClass: 'app-topbar--sidebar-collapsed',
            storageKey: 'sidebar-collapsed',
            ...options
        };

        this.isCollapsed = false;
        this.isMobile = window.matchMedia('(max-width: 991.98px)').matches;

        this.init();
    }

    init() {
        if (!this.sidebar) return;

        // Load persisted state
        this.loadState();

        // Setup event listeners
        this.bindEvents();

        // Handle responsive changes
        this.handleResponsive();

        // Initialize tooltips for collapsed state
        this.initTooltips();

        // Announce to screen readers
        this.announceState();
    }

    bindEvents() {
        // Collapse/Expand button
        if (this.collapseBtn) {
            this.collapseBtn.addEventListener('click', () => this.toggle());
        }

        // Mobile offcanvas toggle
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', () => this.toggleMobile());
        }

        // Keyboard navigation
        this.sidebar.addEventListener('keydown', (e) => this.handleKeydown(e));

        // Close mobile sidebar on link click
        this.sidebar.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => this.closeMobile());
        });

        // Window resize
        window.addEventListener('resize', () => this.handleResponsive());

        // Listen for offcanvas events (Bootstrap)
        const offcanvasEl = this.sidebar.closest('.offcanvas') || this.sidebar;
        if (offcanvasEl) {
            offcanvasEl.addEventListener('hidden.bs.offcanvas', () => this.closeMobile());
            offcanvasEl.addEventListener('shown.bs.offcanvas', () => this.trapFocus());
        }
    }

    toggle() {
        this.isCollapsed = !this.isCollapsed;
        this.applyState();
        this.saveState();
        this.announceState();
    }

    toggleMobile() {
        if (this.isMobile) {
            this.sidebar.classList.toggle(this.options.openClass);
            document.body.classList.toggle('sidebar-open');
        }
    }

    closeMobile() {
        this.sidebar.classList.remove(this.options.openClass);
        document.body.classList.remove('sidebar-open');
    }

    applyState() {
        // The collapsed rail is a desktop-only affordance. On mobile the sidebar
        // is a full-width drawer, so a persisted `collapsed` must not be
        // re-applied or every label collapses into an icon-only rail.
        const collapsed = this.isCollapsed && !this.isMobile;

        this.sidebar.classList.toggle(this.options.collapsedClass, collapsed);
        this.topbar?.classList.toggle(this.options.topbarCollapsedClass, collapsed);
        this.mainContent?.classList.toggle(this.options.mainCollapsedClass, collapsed);

        // Storefront shell (marketplace) is offset by CSS, not by an element the
        // module knows about, so mirror the state onto <body>.
        document.body.classList.toggle('sidebar-collapsed', collapsed);

        if (!this.collapseBtn) {
            return;
        }

        const action = collapsed ? 'Perkecil' : 'Perlebar';

        this.collapseBtn.setAttribute('aria-expanded', String(collapsed));
        this.collapseBtn.setAttribute('aria-label', `${action} sidebar`);

        const label = this.collapseBtn.querySelector('span');
        if (label) {
            label.textContent = action;
        }
    }

    loadState() {
        try {
            const saved = localStorage.getItem(this.options.storageKey);
            if (saved !== null) {
                this.isCollapsed = JSON.parse(saved);
            }
        } catch (e) {
            console.warn('Failed to load sidebar state:', e);
        }
        this.applyState();
    }

    saveState() {
        try {
            localStorage.setItem(this.options.storageKey, JSON.stringify(this.isCollapsed));
        } catch (e) {
            console.warn('Failed to save sidebar state:', e);
        }
    }

    handleResponsive() {
        const wasMobile = this.isMobile;
        this.isMobile = window.matchMedia('(max-width: 991.98px)').matches;

        if (wasMobile !== this.isMobile) {
            if (this.isMobile) {
                // Mobile: sidebar becomes offcanvas
                this.sidebar.classList.add('offcanvas', 'offcanvas-start');
                this.sidebar.setAttribute('tabindex', '-1');
                this.mainContent?.classList.remove(this.options.mainCollapsedClass);
                this.applyState();
            } else {
                // Desktop: restore sidebar
                this.sidebar.classList.remove('offcanvas', 'offcanvas-start', this.options.openClass);
                this.sidebar.removeAttribute('tabindex');
                document.body.classList.remove('sidebar-open');
                this.applyState();
            }
        }
    }

    handleKeydown(e) {
        const links = Array.from(this.sidebar.querySelectorAll('.nav-link:not(:disabled)'));
        const currentIndex = links.findIndex(link => link === document.activeElement);

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.focusNext(links, currentIndex);
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.focusPrev(links, currentIndex);
                break;
            case 'Home':
                e.preventDefault();
                links[0]?.focus();
                break;
            case 'End':
                e.preventDefault();
                links[links.length - 1]?.focus();
                break;
            case 'Enter':
            case ' ':
                if (e.target === this.collapseBtn) {
                    e.preventDefault();
                    this.toggle();
                }
                break;
            case 'Escape':
                if (this.isMobile && this.sidebar.classList.contains(this.options.openClass)) {
                    this.closeMobile();
                    this.toggleBtn?.focus();
                }
                break;
        }
    }

    focusNext(links, currentIndex) {
        const nextIndex = (currentIndex + 1) % links.length;
        links[nextIndex]?.focus();
    }

    focusPrev(links, currentIndex) {
        const prevIndex = (currentIndex - 1 + links.length) % links.length;
        links[prevIndex]?.focus();
    }

    trapFocus() {
        const focusableElements = this.sidebar.querySelectorAll(
            'a[href], button, textarea, input, select, [tabindex]:not([tabindex="-1"])'
        );
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        const handleTab = (e) => {
            if (e.key !== 'Tab') return;

            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement?.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement?.focus();
                }
            }
        };

        this.sidebar.addEventListener('keydown', handleTab);
        this.sidebar._trapFocusHandler = handleTab;
    }

    initTooltips() {
        // Native CSS tooltips are handled via CSS ::after pseudo-elements
        // This method can be extended for more complex tooltip behavior if needed
    }

    announceState() {
        // Create or update live region for screen readers
        let liveRegion = document.getElementById('sidebar-announcer');
        if (!liveRegion) {
            liveRegion = document.createElement('div');
            liveRegion.id = 'sidebar-announcer';
            liveRegion.setAttribute('role', 'status');
            liveRegion.setAttribute('aria-live', 'polite');
            liveRegion.setAttribute('aria-atomic', 'true');
            liveRegion.style.position = 'absolute';
            liveRegion.style.width = '1px';
            liveRegion.style.height = '1px';
            liveRegion.style.padding = '0';
            liveRegion.style.margin = '-1px';
            liveRegion.style.overflow = 'hidden';
            liveRegion.style.clip = 'rect(0, 0, 0, 0)';
            liveRegion.style.whiteSpace = 'nowrap';
            liveRegion.style.border = '0';
            document.body.appendChild(liveRegion);
        }

        const message = this.isCollapsed
            ? 'Sidebar diperkecil. Klik tombol perlebar untuk menampilkan menu lengkap.'
            : 'Sidebar diperluas. Menu navigasi lengkap tersedia.';

        liveRegion.textContent = message;
    }

    // Public API
    expand() {
        if (this.isCollapsed) this.toggle();
    }

    collapse() {
        if (!this.isCollapsed) this.toggle();
    }

    destroy() {
        if (this.collapseBtn) {
            this.collapseBtn.removeEventListener('click', () => this.toggle());
        }
        if (this.toggleBtn) {
            this.toggleBtn.removeEventListener('click', () => this.toggleMobile());
        }
        window.removeEventListener('resize', () => this.handleResponsive());
        if (this.sidebar._trapFocusHandler) {
            this.sidebar.removeEventListener('keydown', this.sidebar._trapFocusHandler);
        }
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.sidebar = new Sidebar();
});

// Export for module usage
export default Sidebar;