(function () {
    'use strict';

    function prefersReducedMotion() {
        return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    /**
     * Wires up one Table of Contents instance: clicking a link scrolls to
     * (and moves focus onto) its target heading instead of only jumping
     * the URL hash, and an IntersectionObserver marks whichever section is
     * currently in view via aria-current — without an aria-live region, so
     * it never triggers a screen-reader announcement on every scroll tick.
     *
     * @param {HTMLElement} toc The .frbl-toc (or .frbl-toc--collapsible details) root.
     */
    function initToc(toc) {
        const links = Array.from(toc.querySelectorAll('.frbl-toc__link'));
        if (!links.length) return;

        const targets = [];
        links.forEach((link) => {
            const id = link.getAttribute('href').slice(1);
            const heading = id ? document.getElementById(id) : null;
            if (heading) targets.push({ link, heading });
        });

        links.forEach((link) => {
            link.addEventListener('click', (event) => {
                const id = link.getAttribute('href').slice(1);
                const heading = id ? document.getElementById(id) : null;
                if (!heading) return;

                event.preventDefault();

                if (window.history && window.history.pushState) {
                    window.history.pushState(null, '', '#' + id);
                }

                heading.scrollIntoView({
                    behavior: prefersReducedMotion() ? 'auto' : 'smooth',
                    block: 'start'
                });

                // Move focus to the destination so keyboard/screen-reader
                // users land there too, not just the viewport.
                heading.focus({ preventScroll: true });
            });
        });

        if (!targets.length || typeof IntersectionObserver === 'undefined') return;

        let activeLink = null;

        function setActive(link) {
            if (activeLink === link) return;
            if (activeLink) activeLink.removeAttribute('aria-current');
            if (link) link.setAttribute('aria-current', 'location');
            activeLink = link;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                const visible = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);

                if (visible.length) {
                    const match = targets.find((target) => target.heading === visible[0].target);
                    if (match) setActive(match.link);
                }
            },
            { rootMargin: '0px 0px -70% 0px', threshold: 0 }
        );

        targets.forEach((target) => observer.observe(target.heading));
    }

    function init() {
        document.querySelectorAll('.frbl-toc').forEach(initToc);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
