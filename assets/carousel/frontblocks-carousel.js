window.addEventListener('load', function (event) {
    const carouselItem = document.querySelectorAll('.frontblocks-carousel');

    if (carouselItem.length > 0) {
        carouselItem.forEach((item) => {
            // Check if carousel should be disabled on desktop.
            const disableOnDesktop = item.getAttribute('data-disable-on-desktop') === 'true';
            const isDesktop = window.innerWidth >= 768;

            // Skip initialization if disabled on desktop and current viewport is desktop.
            if (disableOnDesktop && isDesktop) {
                return;
            }

            // First Parent.
            const parent = item.parentNode;
            const wrapper = document.createElement('div');

            parent.replaceChild(wrapper, item);
            wrapper.appendChild(item);

            wrapper.classList.add('glide__track');
            wrapper.setAttribute('data-glide-el', 'track');

            // Second Parent.
            const parentwrap = wrapper.parentNode;
            const wrapperParent = document.createElement('div');

            parentwrap.replaceChild(wrapperParent, wrapper);
            wrapperParent.appendChild(wrapper);
            wrapperParent.classList.add('frontblocks', 'glide');

            // Options
            const carouselType = item.getAttribute('data-type') ? item.getAttribute('data-type') : 'carousel';
            const carouselbuttons = item.getAttribute('data-buttons') ? item.getAttribute('data-buttons') : 'bullets';
            const carouselView = item.getAttribute('data-view') ? parseInt(item.getAttribute('data-view')) : 4;
            const carouselLaptopView = item.getAttribute('data-laptop-view') ? parseInt(item.getAttribute('data-laptop-view')) : 3;
            const carouselTabletView = item.getAttribute('data-tablet-view') ? parseInt(item.getAttribute('data-tablet-view')) : 2;
            const carouselMobileView = item.getAttribute('data-mobile-view') ? parseInt(item.getAttribute('data-mobile-view')) : 1;
            const autoplayAttr = item.getAttribute('data-autoplay');
            const carouselAutoplay = (autoplayAttr !== '' && autoplayAttr !== null && autoplayAttr !== undefined) ? parseInt(autoplayAttr, 10) : 0;
            const carouselGap = item.getAttribute('data-gap') ? parseInt(item.getAttribute('data-gap'), 10) : 20;
            const carouselRewind = item.getAttribute('data-rewind') ? item.getAttribute('data-rewind') : false;
            const carouselbuttonsColor = item.getAttribute('data-buttons-color') ? item.getAttribute('data-buttons-color') : 'black';
            const carouselbuttonsBackgroundColor = item.getAttribute('data-buttons-background-color') ? item.getAttribute('data-buttons-background-color') : 'transparent';
            const carouselbuttonsPosition = item.getAttribute('data-buttons-position') ? item.getAttribute('data-buttons-position') : 'side';
            const carouselArrowLeftUrl = item.getAttribute('data-arrow-left-url') || '';
            const carouselArrowRightUrl = item.getAttribute('data-arrow-right-url') || '';
            const carouselPauseOnHover = item.getAttribute('data-pause-on-hover') !== 'false';

            // Never auto-start motion for visitors who asked the OS/browser
            // for reduced motion, regardless of the configured autoplay value.
            const prefersReducedMotion = window.matchMedia
                && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const effectiveAutoplay = prefersReducedMotion ? 0 : carouselAutoplay;


            // Add classes
            item.classList.add('glide__slides', 'glide-' + Math.floor(Math.random() * 1000));
            // Force flex layout via inline style to beat WP grid CSS (columns-N, is-layout-grid).
            if (item.classList.contains('wp-block-post-template')) {
                item.style.setProperty('display', 'flex', 'important');
                item.style.setProperty('flex-wrap', 'nowrap', 'important');
                item.style.setProperty('max-width', 'none', 'important');
                item.style.setProperty('grid-template-columns', 'none', 'important');
                item.style.setProperty('list-style', 'none', 'important');
                item.style.setProperty('padding-left', '0', 'important');
                item.style.setProperty('margin-left', '0', 'important');
            }
            for (const child of item.children) {
                child.classList.add('glide__slide');
            }

            // Don't show bullets on responsive and more than 10 items.
            let showBullets = false;
            if (window.screen.availWidth < 768 && item.children.length <= 10) {
                showBullets = true;
            } else if (window.screen.availWidth > 768) {
                showBullets = true;
            }

            if (showBullets && carouselbuttons == 'bullets') {
                const bullets = document.createElement('div');
                bullets.classList.add('glide__bullets');
                bullets.setAttribute('data-glide-el', 'controls[nav]');
                bullets.setAttribute('role', 'group');
                bullets.setAttribute('aria-label', 'Slide navigation');

                for (let i = 0; i < item.children.length; i++) {
                    const bullet = document.createElement('button');
                    bullet.classList.add('glide__bullet');
                    bullet.setAttribute('data-glide-dir', '=' + i);
                    bullet.setAttribute('aria-label', 'Go to slide ' + (i + 1));
                    bullets.appendChild(bullet);
                }

                wrapperParent.appendChild(bullets);

                // Set bullet colors via CSS custom properties on the wrapper.
                // This avoids specificity conflicts with the stylesheet.
                if (carouselbuttonsColor) {
                    wrapperParent.style.setProperty('--frbl-bullet-color', carouselbuttonsColor);
                }
                if (carouselbuttonsBackgroundColor && carouselbuttonsBackgroundColor !== 'transparent') {
                    wrapperParent.style.setProperty('--frbl-bullet-bg', carouselbuttonsBackgroundColor);
                }
            }

            if (carouselbuttons == 'arrows') {
                const arrows = document.createElement('div');
                arrows.classList.add('glide__arrows');

                const positionClassMap = {
                    'side':         'glide__arrows--side',
                    'bottom':       'glide__arrows--bottom-left',
                    'bottom-left':  'glide__arrows--bottom-left',
                    'bottom-right': 'glide__arrows--bottom-right',
                    'top-left':     'glide__arrows--top-left',
                    'top-right':    'glide__arrows--top-right',
                };
                arrows.classList.add(positionClassMap[carouselbuttonsPosition] || 'glide__arrows--side');

                arrows.setAttribute('data-glide-el', 'controls');

                const defaultLeftSvg = '<svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 1L1 6L6 11" stroke="' + carouselbuttonsColor + '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                const defaultRightSvg = '<svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 11L6 6L1 1" stroke="' + carouselbuttonsColor + '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                const leftSvg = carouselArrowLeftUrl ? '<img src="' + carouselArrowLeftUrl + '" alt="" aria-hidden="true">' : defaultLeftSvg;
                const rightSvg = carouselArrowRightUrl ? '<img src="' + carouselArrowRightUrl + '" alt="" aria-hidden="true">' : defaultRightSvg;

                arrowsHTML = '<button class="glide__arrow glide__arrow--left" data-glide-dir="<"';
                arrowsHTML += ' aria-label="Previous slide"';
                arrowsHTML += ' style="background-color: ' + carouselbuttonsBackgroundColor + '"';
                arrowsHTML += '>' + leftSvg + '</button>';
                arrowsHTML += '<button class="glide__arrow glide__arrow--right" data-glide-dir=">"';
                arrowsHTML += ' aria-label="Next slide"';
                arrowsHTML += ' style="background-color: ' + carouselbuttonsBackgroundColor + '"';
                arrowsHTML += '>' + rightSvg + '</button>';
                arrows.innerHTML = arrowsHTML;
                wrapperParent.appendChild(arrows);
            }

            // Accessible pause/resume control. Rendered whenever autoplay is
            // actually running, independent of the "pause on hover/focus"
            // setting above — visitors must always have a way to stop
            // auto-advancing content (WCAG 2.2.2), not just a convenience
            // behavior tied to their pointer/keyboard.
            let pauseButton = null;
            const PAUSE_LABEL = 'Pause automatic slideshow';
            const PLAY_LABEL = 'Play automatic slideshow';
            const pauseIcon = '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="1" y="1" width="3.5" height="10" rx="1" fill="currentColor"/><rect x="7.5" y="1" width="3.5" height="10" rx="1" fill="currentColor"/></svg>';
            const playIcon = '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M2 1.2v9.6a.6.6 0 0 0 .92.5l7.6-4.8a.6.6 0 0 0 0-1L2.92.7A.6.6 0 0 0 2 1.2Z" fill="currentColor"/></svg>';

            if (effectiveAutoplay > 0) {
                pauseButton = document.createElement('button');
                pauseButton.type = 'button';
                pauseButton.classList.add('glide__pause');
                wrapperParent.appendChild(pauseButton);
            }

            const glideFrontBlocks = new Glide(wrapperParent, {
                type: carouselType,
                perView: carouselView,
                startAt: 0,
                autoplay: effectiveAutoplay > 0 ? effectiveAutoplay : false,
                // Hover/focus pausing is handled manually below (see
                // pauseReasons) so it can be coordinated with the manual
                // pause/resume button without the two fighting over Glide's
                // internal play/pause state.
                hoverpause: false,
                gap: isNaN(carouselGap) ? 20 : carouselGap,
                rewind: carouselRewind,
                breakpoints: {
                    768: {
                        perView: carouselMobileView
                    },
                    1024: {
                        perView: carouselTabletView
                    },
                    1440: {
                        perView: carouselLaptopView
                    }
                }
            });

            if ('slider' === carouselType) {
                glideFrontBlocks.on('run.after', () => {
                    const currentIndex = glideFrontBlocks.index;
                    const lastIndex = glideFrontBlocks.selector.querySelectorAll('.glide__slide').length;

                    actualView = parseInt(currentIndex) + parseInt(carouselView);

                    if (actualView > lastIndex) {
                        setTimeout(() => {
                            glideFrontBlocks.go('=0');
                        }, 5);
                    }
                });
            }

            glideFrontBlocks.mount();

            if (effectiveAutoplay > 0) {
                // Coordinates every reason autoplay might be paused (hover,
                // keyboard/AT focus, and the manual pause button) so they
                // can't fight over Glide's play/pause state — autoplay only
                // resumes once none of them apply any more.
                const pauseReasons = new Set();

                const updatePauseButton = () => {
                    if (!pauseButton) return;
                    const paused = pauseReasons.has('manual');
                    pauseButton.setAttribute('aria-pressed', String(paused));
                    pauseButton.setAttribute('aria-label', paused ? PLAY_LABEL : PAUSE_LABEL);
                    pauseButton.innerHTML = paused ? playIcon : pauseIcon;
                };

                const applyPauseState = () => {
                    if (pauseReasons.size > 0) {
                        glideFrontBlocks.pause();
                    } else {
                        glideFrontBlocks.play();
                    }
                    updatePauseButton();
                };

                if (carouselPauseOnHover) {
                    wrapperParent.addEventListener('mouseenter', () => { pauseReasons.add('hover'); applyPauseState(); });
                    wrapperParent.addEventListener('mouseleave', () => { pauseReasons.delete('hover'); applyPauseState(); });
                    wrapperParent.addEventListener('focusin', () => { pauseReasons.add('focus'); applyPauseState(); });
                    wrapperParent.addEventListener('focusout', () => { pauseReasons.delete('focus'); applyPauseState(); });
                }

                if (pauseButton) {
                    pauseButton.addEventListener('click', () => {
                        if (pauseReasons.has('manual')) {
                            pauseReasons.delete('manual');
                        } else {
                            pauseReasons.add('manual');
                        }
                        applyPauseState();
                    });
                }

                updatePauseButton();
            }
        });
    }
});
